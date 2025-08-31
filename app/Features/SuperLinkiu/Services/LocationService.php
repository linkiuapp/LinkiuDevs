<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Location Service for Geographic Autocomplete
 * 
 * Provides location search functionality with caching and fallback data
 * Requirements: 2.4 - Geographic autocomplete for location fields
 */
class LocationService
{
    private array $countries;
    private array $departments;
    private array $cities;

    public function __construct()
    {
        $this->loadStaticData();
    }

    /**
     * Search locations by query and type
     */
    public function searchLocations(string $query, string $type = 'all', string $country = null, string $department = null): array
    {
        $query = strtolower(trim($query));
        
        if (strlen($query) < 2) {
            return [];
        }

        $cacheKey = "location_search_{$type}_{$country}_{$department}_{$query}";
        
        return Cache::remember($cacheKey, 3600, function () use ($query, $type, $country, $department) {
            switch ($type) {
                case 'countries':
                    return $this->searchCountries($query);
                case 'departments':
                    return $this->searchDepartments($query, $country);
                case 'cities':
                    return $this->searchCities($query, $country, $department);
                default:
                    return $this->searchAll($query);
            }
        });
    }

    /**
     * Search countries
     */
    private function searchCountries(string $query): array
    {
        $results = [];
        
        foreach ($this->countries as $country) {
            if (str_contains(strtolower($country['name']), $query) || 
                str_contains(strtolower($country['code']), $query)) {
                $results[] = [
                    'type' => 'country',
                    'code' => $country['code'],
                    'name' => $country['name'],
                    'flag' => $country['flag'] ?? null,
                    'display' => $country['name']
                ];
            }
        }

        return array_slice($results, 0, 10);
    }

    /**
     * Search departments/states
     */
    private function searchDepartments(string $query, ?string $country = null): array
    {
        $results = [];
        $departments = $country ? ($this->departments[$country] ?? []) : $this->getAllDepartments();
        
        foreach ($departments as $department) {
            if (str_contains(strtolower($department['name']), $query)) {
                $results[] = [
                    'type' => 'department',
                    'code' => $department['code'],
                    'name' => $department['name'],
                    'country' => $department['country'] ?? $country,
                    'display' => $department['name']
                ];
            }
        }

        return array_slice($results, 0, 15);
    }

    /**
     * Search cities
     */
    private function searchCities(string $query, ?string $country = null, ?string $department = null): array
    {
        $results = [];
        $cities = $this->getCitiesForSearch($country, $department);
        
        foreach ($cities as $city) {
            if (str_contains(strtolower($city['name']), $query)) {
                $results[] = [
                    'type' => 'city',
                    'code' => $city['code'],
                    'name' => $city['name'],
                    'department' => $city['department'],
                    'country' => $city['country'],
                    'display' => $city['name'] . ', ' . $city['department']
                ];
            }
        }

        return array_slice($results, 0, 20);
    }

    /**
     * Search all location types
     */
    private function searchAll(string $query): array
    {
        $results = [];
        
        // Search countries first
        $countries = $this->searchCountries($query);
        $results = array_merge($results, array_slice($countries, 0, 3));
        
        // Search departments
        $departments = $this->searchDepartments($query);
        $results = array_merge($results, array_slice($departments, 0, 5));
        
        // Search cities
        $cities = $this->searchCities($query);
        $results = array_merge($results, array_slice($cities, 0, 10));
        
        return $results;
    }

    /**
     * Get departments for a specific country
     */
    public function getDepartmentsByCountry(string $countryCode): array
    {
        return $this->departments[$countryCode] ?? [];
    }

    /**
     * Get cities for a specific department
     */
    public function getCitiesByDepartment(string $countryCode, string $departmentCode): array
    {
        $cacheKey = "cities_{$countryCode}_{$departmentCode}";
        
        return Cache::remember($cacheKey, 7200, function () use ($countryCode, $departmentCode) {
            $cities = $this->cities[$countryCode][$departmentCode] ?? [];
            
            return array_map(function ($city) use ($countryCode, $departmentCode) {
                return [
                    'code' => $city['code'],
                    'name' => $city['name'],
                    'department' => $this->getDepartmentName($countryCode, $departmentCode),
                    'country' => $this->getCountryName($countryCode)
                ];
            }, $cities);
        });
    }

    /**
     * Validate location data
     */
    public function validateLocation(array $location): array
    {
        $errors = [];
        
        // Validate country
        if (!empty($location['country'])) {
            $countryExists = collect($this->countries)->contains('name', $location['country']);
            if (!$countryExists) {
                $errors['country'] = 'País no válido';
            }
        }
        
        // Validate department if country is provided
        if (!empty($location['country']) && !empty($location['department'])) {
            $countryCode = $this->getCountryCode($location['country']);
            if ($countryCode) {
                $departments = $this->getDepartmentsByCountry($countryCode);
                $departmentExists = collect($departments)->contains('name', $location['department']);
                if (!$departmentExists) {
                    $errors['department'] = 'Departamento no válido para el país seleccionado';
                }
            }
        }
        
        // Validate city if department is provided
        if (!empty($location['country']) && !empty($location['department']) && !empty($location['city'])) {
            $countryCode = $this->getCountryCode($location['country']);
            $departmentCode = $this->getDepartmentCode($countryCode, $location['department']);
            
            if ($countryCode && $departmentCode) {
                $cities = $this->getCitiesByDepartment($countryCode, $departmentCode);
                $cityExists = collect($cities)->contains('name', $location['city']);
                if (!$cityExists) {
                    $errors['city'] = 'Ciudad no válida para el departamento seleccionado';
                }
            }
        }
        
        return $errors;
    }

    /**
     * Format address components
     */
    public function formatAddress(array $components): string
    {
        $parts = array_filter([
            $components['city'] ?? null,
            $components['department'] ?? null,
            $components['country'] ?? null
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get location suggestions based on partial input
     */
    public function getLocationSuggestions(string $input, int $limit = 5): array
    {
        $suggestions = [];
        
        // Try to parse the input
        $parts = array_map('trim', explode(',', $input));
        
        if (count($parts) === 1) {
            // Single part - could be city, department, or country
            $query = $parts[0];
            $suggestions = array_merge(
                $this->searchCities($query),
                $this->searchDepartments($query),
                $this->searchCountries($query)
            );
        } elseif (count($parts) === 2) {
            // Two parts - likely city, department
            $cityQuery = $parts[0];
            $departmentQuery = $parts[1];
            
            // Find matching departments first
            $departments = $this->searchDepartments($departmentQuery);
            
            foreach ($departments as $department) {
                $cities = $this->searchCities($cityQuery, $department['country'], $department['code']);
                $suggestions = array_merge($suggestions, $cities);
            }
        }
        
        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Load static location data
     */
    private function loadStaticData(): void
    {
        $this->countries = [
            ['code' => 'CO', 'name' => 'Colombia', 'flag' => '🇨🇴'],
            ['code' => 'US', 'name' => 'Estados Unidos', 'flag' => '🇺🇸'],
            ['code' => 'MX', 'name' => 'México', 'flag' => '🇲🇽'],
            ['code' => 'AR', 'name' => 'Argentina', 'flag' => '🇦🇷'],
            ['code' => 'PE', 'name' => 'Perú', 'flag' => '🇵🇪'],
            ['code' => 'CL', 'name' => 'Chile', 'flag' => '🇨🇱'],
            ['code' => 'EC', 'name' => 'Ecuador', 'flag' => '🇪🇨'],
            ['code' => 'VE', 'name' => 'Venezuela', 'flag' => '🇻🇪'],
            ['code' => 'ES', 'name' => 'España', 'flag' => '🇪🇸'],
            ['code' => 'BR', 'name' => 'Brasil', 'flag' => '🇧🇷'],
            ['code' => 'UY', 'name' => 'Uruguay', 'flag' => '🇺🇾'],
            ['code' => 'PY', 'name' => 'Paraguay', 'flag' => '🇵🇾'],
            ['code' => 'BO', 'name' => 'Bolivia', 'flag' => '🇧🇴'],
        ];

        $this->departments = [
            'CO' => [
                ['code' => 'ANT', 'name' => 'Antioquia', 'country' => 'Colombia'],
                ['code' => 'ATL', 'name' => 'Atlántico', 'country' => 'Colombia'],
                ['code' => 'BOG', 'name' => 'Bogotá D.C.', 'country' => 'Colombia'],
                ['code' => 'BOL', 'name' => 'Bolívar', 'country' => 'Colombia'],
                ['code' => 'BOY', 'name' => 'Boyacá', 'country' => 'Colombia'],
                ['code' => 'CAL', 'name' => 'Caldas', 'country' => 'Colombia'],
                ['code' => 'CAQ', 'name' => 'Caquetá', 'country' => 'Colombia'],
                ['code' => 'CAS', 'name' => 'Casanare', 'country' => 'Colombia'],
                ['code' => 'CAU', 'name' => 'Cauca', 'country' => 'Colombia'],
                ['code' => 'CES', 'name' => 'Cesar', 'country' => 'Colombia'],
                ['code' => 'COR', 'name' => 'Córdoba', 'country' => 'Colombia'],
                ['code' => 'CUN', 'name' => 'Cundinamarca', 'country' => 'Colombia'],
                ['code' => 'HUI', 'name' => 'Huila', 'country' => 'Colombia'],
                ['code' => 'LAG', 'name' => 'La Guajira', 'country' => 'Colombia'],
                ['code' => 'MAG', 'name' => 'Magdalena', 'country' => 'Colombia'],
                ['code' => 'MET', 'name' => 'Meta', 'country' => 'Colombia'],
                ['code' => 'NAR', 'name' => 'Nariño', 'country' => 'Colombia'],
                ['code' => 'NSA', 'name' => 'Norte de Santander', 'country' => 'Colombia'],
                ['code' => 'QUI', 'name' => 'Quindío', 'country' => 'Colombia'],
                ['code' => 'RIS', 'name' => 'Risaralda', 'country' => 'Colombia'],
                ['code' => 'SAN', 'name' => 'Santander', 'country' => 'Colombia'],
                ['code' => 'SUC', 'name' => 'Sucre', 'country' => 'Colombia'],
                ['code' => 'TOL', 'name' => 'Tolima', 'country' => 'Colombia'],
                ['code' => 'VAC', 'name' => 'Valle del Cauca', 'country' => 'Colombia'],
            ],
            'MX' => [
                ['code' => 'AGU', 'name' => 'Aguascalientes', 'country' => 'México'],
                ['code' => 'BCN', 'name' => 'Baja California', 'country' => 'México'],
                ['code' => 'BCS', 'name' => 'Baja California Sur', 'country' => 'México'],
                ['code' => 'CAM', 'name' => 'Campeche', 'country' => 'México'],
                ['code' => 'CHP', 'name' => 'Chiapas', 'country' => 'México'],
                ['code' => 'CHH', 'name' => 'Chihuahua', 'country' => 'México'],
                ['code' => 'CMX', 'name' => 'Ciudad de México', 'country' => 'México'],
                ['code' => 'COA', 'name' => 'Coahuila', 'country' => 'México'],
                ['code' => 'COL', 'name' => 'Colima', 'country' => 'México'],
                ['code' => 'DUR', 'name' => 'Durango', 'country' => 'México'],
                ['code' => 'GUA', 'name' => 'Guanajuato', 'country' => 'México'],
                ['code' => 'GRO', 'name' => 'Guerrero', 'country' => 'México'],
                ['code' => 'HID', 'name' => 'Hidalgo', 'country' => 'México'],
                ['code' => 'JAL', 'name' => 'Jalisco', 'country' => 'México'],
                ['code' => 'MEX', 'name' => 'Estado de México', 'country' => 'México'],
            ],
            'AR' => [
                ['code' => 'BA', 'name' => 'Buenos Aires', 'country' => 'Argentina'],
                ['code' => 'CT', 'name' => 'Catamarca', 'country' => 'Argentina'],
                ['code' => 'CH', 'name' => 'Chaco', 'country' => 'Argentina'],
                ['code' => 'CB', 'name' => 'Córdoba', 'country' => 'Argentina'],
                ['code' => 'CR', 'name' => 'Corrientes', 'country' => 'Argentina'],
                ['code' => 'ER', 'name' => 'Entre Ríos', 'country' => 'Argentina'],
                ['code' => 'JY', 'name' => 'Jujuy', 'country' => 'Argentina'],
                ['code' => 'LP', 'name' => 'La Pampa', 'country' => 'Argentina'],
                ['code' => 'LR', 'name' => 'La Rioja', 'country' => 'Argentina'],
                ['code' => 'MZ', 'name' => 'Mendoza', 'country' => 'Argentina'],
            ]
        ];

        $this->cities = [
            'CO' => [
                'ANT' => [
                    ['code' => 'MED', 'name' => 'Medellín'],
                    ['code' => 'BEL', 'name' => 'Bello'],
                    ['code' => 'ITA', 'name' => 'Itagüí'],
                    ['code' => 'ENV', 'name' => 'Envigado'],
                    ['code' => 'SAB', 'name' => 'Sabaneta'],
                    ['code' => 'CAL', 'name' => 'Caldas'],
                    ['code' => 'COP', 'name' => 'Copacabana'],
                    ['code' => 'GIR', 'name' => 'Girardota'],
                    ['code' => 'BAR', 'name' => 'Barbosa'],
                    ['code' => 'RIO', 'name' => 'Rionegro'],
                ],
                'BOG' => [
                    ['code' => 'BOG', 'name' => 'Bogotá'],
                ],
                'VAC' => [
                    ['code' => 'CAL', 'name' => 'Cali'],
                    ['code' => 'PAL', 'name' => 'Palmira'],
                    ['code' => 'BUE', 'name' => 'Buenaventura'],
                    ['code' => 'TUL', 'name' => 'Tuluá'],
                    ['code' => 'CAR', 'name' => 'Cartago'],
                    ['code' => 'BUG', 'name' => 'Buga'],
                ],
                'ATL' => [
                    ['code' => 'BAQ', 'name' => 'Barranquilla'],
                    ['code' => 'SOL', 'name' => 'Soledad'],
                    ['code' => 'MAL', 'name' => 'Malambo'],
                    ['code' => 'SAB', 'name' => 'Sabanalarga'],
                ],
                'SAN' => [
                    ['code' => 'BUC', 'name' => 'Bucaramanga'],
                    ['code' => 'FLO', 'name' => 'Floridablanca'],
                    ['code' => 'GIR', 'name' => 'Girón'],
                    ['code' => 'PIE', 'name' => 'Piedecuesta'],
                ],
            ],
            'MX' => [
                'CMX' => [
                    ['code' => 'CMX', 'name' => 'Ciudad de México'],
                ],
                'JAL' => [
                    ['code' => 'GDL', 'name' => 'Guadalajara'],
                    ['code' => 'ZAP', 'name' => 'Zapopan'],
                    ['code' => 'TLA', 'name' => 'Tlaquepaque'],
                    ['code' => 'TON', 'name' => 'Tonalá'],
                ],
                'NLE' => [
                    ['code' => 'MTY', 'name' => 'Monterrey'],
                    ['code' => 'GAR', 'name' => 'García'],
                    ['code' => 'APO', 'name' => 'Apodaca'],
                ],
            ],
            'AR' => [
                'BA' => [
                    ['code' => 'CABA', 'name' => 'Ciudad Autónoma de Buenos Aires'],
                    ['code' => 'LAP', 'name' => 'La Plata'],
                    ['code' => 'MAR', 'name' => 'Mar del Plata'],
                    ['code' => 'BAH', 'name' => 'Bahía Blanca'],
                ],
                'CB' => [
                    ['code' => 'COR', 'name' => 'Córdoba'],
                    ['code' => 'RIO', 'name' => 'Río Cuarto'],
                    ['code' => 'VIL', 'name' => 'Villa María'],
                ],
            ]
        ];
    }

    /**
     * Helper methods
     */
    private function getAllDepartments(): array
    {
        $all = [];
        foreach ($this->departments as $countryCode => $departments) {
            foreach ($departments as $department) {
                $all[] = array_merge($department, ['country_code' => $countryCode]);
            }
        }
        return $all;
    }

    private function getCitiesForSearch(?string $country = null, ?string $department = null): array
    {
        $all = [];
        
        $countries = $country ? [$country => $this->cities[$country] ?? []] : $this->cities;
        
        foreach ($countries as $countryCode => $countryDepartments) {
            $departments = $department ? [$department => $countryDepartments[$department] ?? []] : $countryDepartments;
            
            foreach ($departments as $departmentCode => $cities) {
                foreach ($cities as $city) {
                    $all[] = array_merge($city, [
                        'country' => $this->getCountryName($countryCode),
                        'department' => $this->getDepartmentName($countryCode, $departmentCode),
                        'country_code' => $countryCode,
                        'department_code' => $departmentCode
                    ]);
                }
            }
        }
        
        return $all;
    }

    private function getCountryName(string $code): string
    {
        $country = collect($this->countries)->firstWhere('code', $code);
        return $country['name'] ?? $code;
    }

    private function getCountryCode(string $name): ?string
    {
        $country = collect($this->countries)->firstWhere('name', $name);
        return $country['code'] ?? null;
    }

    private function getDepartmentName(string $countryCode, string $departmentCode): string
    {
        $departments = $this->departments[$countryCode] ?? [];
        $department = collect($departments)->firstWhere('code', $departmentCode);
        return $department['name'] ?? $departmentCode;
    }

    private function getDepartmentCode(string $countryCode, string $departmentName): ?string
    {
        $departments = $this->departments[$countryCode] ?? [];
        $department = collect($departments)->firstWhere('name', $departmentName);
        return $department['code'] ?? null;
    }
}