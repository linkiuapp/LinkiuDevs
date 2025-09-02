<?php

namespace App\Features\SuperLinkiu\Services;

use App\Shared\Models\Store;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use App\Core\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StoreValidationService
{
    /**
     * Validar disponibilidad de email en tiempo real
     */
    public function validateEmailAvailability(string $email, ?int $excludeStoreId = null): array
    {
        // Verificar que sea un email válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'available' => false,
                'message' => 'Formato de email inválido'
            ];
        }

        // Verificar disponibilidad en stores
        $storeQuery = Store::where('email', $email);
        if ($excludeStoreId) {
            $storeQuery->where('id', '!=', $excludeStoreId);
        }
        
        if ($storeQuery->exists()) {
            return [
                'available' => false,
                'message' => 'Este email ya está registrado por otra tienda'
            ];
        }

        // Verificar disponibilidad en users (admins)
        $userQuery = User::where('email', $email);
        if ($userQuery->exists()) {
            return [
                'available' => false,
                'message' => 'Este email ya está registrado como administrador'
            ];
        }

        return [
            'available' => true,
            'message' => 'Email disponible'
        ];
    }

    /**
     * Validar disponibilidad de slug en tiempo real
     */
    public function validateSlugAvailability(string $slug, ?int $excludeStoreId = null): array
    {
        // Sanitizar el slug
        $cleanSlug = $this->sanitizeSlug($slug);
        
        // Verificar formato
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $cleanSlug)) {
            return [
                'available' => false,
                'message' => 'URL inválida. Solo se permiten letras, números y guiones.',
                'suggested' => $cleanSlug
            ];
        }

        // Verificar si está reservado
        if (RouteServiceProvider::isReservedSlug($cleanSlug)) {
            return [
                'available' => false,
                'message' => 'Esta URL está reservada por el sistema',
                'suggested' => $cleanSlug . '-store'
            ];
        }

        // Verificar disponibilidad en BD
        $query = Store::where('slug', $cleanSlug);
        if ($excludeStoreId) {
            $query->where('id', '!=', $excludeStoreId);
        }

        if ($query->exists()) {
            return [
                'available' => false,
                'message' => 'Esta URL ya está en uso por otra tienda',
                'suggested' => $this->suggestAlternativeSlug($cleanSlug)
            ];
        }

        return [
            'available' => true,
            'message' => 'URL disponible',
            'clean' => $cleanSlug
        ];
    }

    /**
     * Sugerir slug alternativo cuando uno está ocupado
     */
    public function suggestAlternativeSlug(string $baseSlug): string
    {
        $counter = 1;
        
        do {
            $suggested = $baseSlug . '-' . $counter;
            $exists = Store::where('slug', $suggested)->exists() || RouteServiceProvider::isReservedSlug($suggested);
            $counter++;
        } while ($exists && $counter <= 100);

        return $suggested;
    }

    /**
     * Generar sugerencias de slug basadas en nombre de tienda
     */
    public function suggestSlugFromName(string $storeName): array
    {
        $baseSuggestions = [
            $this->sanitizeSlug($storeName),
            $this->sanitizeSlug($storeName . '-store'),
            $this->sanitizeSlug($storeName . '-shop'),
            $this->sanitizeSlug(Str::words($storeName, 2, '')), // Primeras 2 palabras
        ];

        $suggestions = [];
        
        foreach ($baseSuggestions as $suggestion) {
            if (empty($suggestion)) continue;
            
            $validation = $this->validateSlugAvailability($suggestion);
            
            if ($validation['available']) {
                $suggestions[] = [
                    'slug' => $suggestion,
                    'available' => true
                ];
            } else {
                $alternative = $this->suggestAlternativeSlug($suggestion);
                $suggestions[] = [
                    'slug' => $alternative,
                    'available' => true,
                    'note' => 'Alternativa sugerida'
                ];
            }
        }

        // Remover duplicados y limitar a 5 sugerencias
        $unique = collect($suggestions)->unique('slug')->take(5)->values();

        return $unique->toArray();
    }

    /**
     * Validar configuración fiscal
     */
    public function validateFiscalData(array $data): array
    {
        $issues = [];

        // Si tiene documento fiscal, validar coherencia
        if (!empty($data['document_type']) && !empty($data['document_number'])) {
            if ($data['document_type'] === 'nit') {
                // Validar formato NIT colombiano básico
                if (!preg_match('/^\d{8,10}-?\d$/', $data['document_number'])) {
                    $issues[] = 'El formato del NIT no parece válido para Colombia';
                }
            }
        }

        // Validar coherencia entre país y datos fiscales
        if (isset($data['country']) && strtolower($data['country']) === 'colombia') {
            if (isset($data['document_type']) && $data['document_type'] === 'nit') {
                if (empty($data['document_number'])) {
                    $issues[] = 'Para empresas en Colombia con NIT, el número es obligatorio';
                }
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'suggestions' => $this->getFiscalSuggestions($data)
        ];
    }

    /**
     * Obtener sugerencias fiscales
     */
    private function getFiscalSuggestions(array $data): array
    {
        $suggestions = [];

        if (isset($data['country']) && strtolower($data['country']) === 'colombia') {
            $suggestions[] = 'Para Colombia: usa NIT para empresas o cédula para personas naturales';
        }

        return $suggestions;
    }

    /**
     * Sanitizar slug (método público para uso en otros lugares)
     */
    public function sanitizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        
        // Eliminar acentos
        $accents = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        $slug = strtr($slug, $accents);
        
        // Reemplazar espacios y caracteres especiales con guiones
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        
        // Limpiar guiones
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Validar longitud
        if (strlen($slug) > 50) {
            $slug = substr($slug, 0, 50);
            $slug = rtrim($slug, '-');
        }
        
        return $slug ?: 'tienda-' . rand(1000, 9999);
    }
}
