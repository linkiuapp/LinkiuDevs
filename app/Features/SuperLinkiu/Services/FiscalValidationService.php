<?php

namespace App\Features\SuperLinkiu\Services;

/**
 * Fiscal Validation Service
 * 
 * Handles business document validation and tax information validation
 * Requirements: 3.3, 3.4 - Business document validation with country-specific rules
 */
class FiscalValidationService
{
    /**
     * Document validation patterns by type and country
     */
    private const DOCUMENT_PATTERNS = [
        'nit' => [
            'Colombia' => '/^\d{9}-?\d$/',
            'pattern' => '/^\d{9,10}$/',
            'description' => 'NIT debe tener 9 dígitos + dígito de verificación'
        ],
        'rut' => [
            'Chile' => '/^\d{8}-?[0-9kK]$/',
            'pattern' => '/^\d{8}[0-9kK]$/',
            'description' => 'RUT debe tener 8 dígitos + dígito verificador'
        ],
        'rfc' => [
            'México' => '/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/',
            'pattern' => '/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/',
            'description' => 'RFC debe tener 4 letras + 6 números + 3 caracteres'
        ],
        'cedula' => [
            'pattern' => '/^\d{6,10}$/',
            'description' => 'Cédula debe tener entre 6 y 10 dígitos'
        ]
    ];

    /**
     * Tax regimes by country
     */
    private const TAX_REGIMES = [
        'Colombia' => [
            'simplificado' => 'Régimen Simplificado',
            'comun' => 'Régimen Común',
            'gran_contribuyente' => 'Gran Contribuyente',
            'no_responsable' => 'No Responsable de IVA'
        ],
        'México' => [
            'general' => 'Régimen General',
            'incorporacion_fiscal' => 'Régimen de Incorporación Fiscal',
            'actividades_empresariales' => 'Actividades Empresariales y Profesionales'
        ],
        'Argentina' => [
            'monotributo' => 'Monotributo',
            'responsable_inscripto' => 'Responsable Inscripto',
            'exento' => 'Exento'
        ],
        'Chile' => [
            'primera_categoria' => 'Primera Categoría',
            'segunda_categoria' => 'Segunda Categoría',
            'pro_pyme' => 'Régimen Pro PyME'
        ]
    ];

    /**
     * Validate fiscal document number
     */
    public function validateFiscalDocument(string $documentType, string $documentNumber, string $country = null): array
    {
        $result = [
            'isValid' => false,
            'message' => '',
            'suggestions' => []
        ];

        if (empty($documentNumber) || empty($documentType)) {
            $result['message'] = 'Tipo y número de documento son requeridos';
            return $result;
        }

        $patterns = self::DOCUMENT_PATTERNS[$documentType] ?? null;
        if (!$patterns) {
            $result['message'] = 'Tipo de documento no válido';
            return $result;
        }

        // Clean the document number
        $cleanNumber = $this->cleanDocumentNumber($documentNumber, $documentType);

        // Check country-specific pattern first
        if ($country && isset($patterns[$country])) {
            $isValid = preg_match($patterns[$country], $documentNumber);
        } else {
            // Fall back to general pattern
            if ($documentType === 'rfc') {
                // RFC needs uppercase validation
                $isValid = preg_match($patterns['pattern'], strtoupper($documentNumber));
            } else {
                $isValid = preg_match($patterns['pattern'], $cleanNumber);
            }
        }

        if ($isValid) {
            $result['isValid'] = true;
            $result['message'] = 'Documento fiscal válido';
        } else {
            $result['message'] = $patterns['description'];
            $result['suggestions'] = $this->generateDocumentSuggestions($documentType, $documentNumber);
        }

        return $result;
    }

    /**
     * Validate NIT with verification digit (Colombia)
     */
    public function validateNitWithVerificationDigit(string $nit): bool
    {
        // Remove any non-numeric characters except the last character
        $cleanNit = preg_replace('/[^0-9]/', '', substr($nit, 0, -1)) . substr($nit, -1);
        
        if (strlen($cleanNit) !== 10) {
            return false;
        }

        $number = substr($cleanNit, 0, 9);
        $verificationDigit = substr($cleanNit, -1);

        // Calculate verification digit
        $weights = [3, 7, 13, 17, 19, 23, 29, 37, 41];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += intval($number[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;
        $calculatedDigit = $remainder < 2 ? $remainder : 11 - $remainder;

        return $calculatedDigit == intval($verificationDigit);
    }

    /**
     * Validate RUT with verification digit (Chile)
     */
    public function validateRutWithVerificationDigit(string $rut): bool
    {
        // Clean and format RUT
        $cleanRut = preg_replace('/[^0-9kK]/', '', $rut);
        
        if (strlen($cleanRut) !== 9) {
            return false;
        }

        $number = substr($cleanRut, 0, 8);
        $verificationDigit = strtoupper(substr($cleanRut, -1));

        // Calculate verification digit
        $sum = 0;
        $multiplier = 2;

        for ($i = 7; $i >= 0; $i--) {
            $sum += intval($number[$i]) * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $remainder = $sum % 11;
        $calculatedDigit = 11 - $remainder;

        if ($calculatedDigit === 11) {
            $calculatedDigit = '0';
        } elseif ($calculatedDigit === 10) {
            $calculatedDigit = 'K';
        } else {
            $calculatedDigit = strval($calculatedDigit);
        }

        return $calculatedDigit === $verificationDigit;
    }

    /**
     * Get available tax regimes for a country
     */
    public function getTaxRegimes(string $country): array
    {
        return self::TAX_REGIMES[$country] ?? [
            'general' => 'Régimen General',
            'simplificado' => 'Régimen Simplificado'
        ];
    }

    /**
     * Validate tax regime for country
     */
    public function validateTaxRegime(string $taxRegime, string $country): bool
    {
        $availableRegimes = $this->getTaxRegimes($country);
        return array_key_exists($taxRegime, $availableRegimes);
    }

    /**
     * Validate complete fiscal information
     */
    public function validateFiscalInformation(array $fiscalData): array
    {
        $errors = [];

        // Required fields validation
        $requiredFields = [
            'fiscal_document_type' => 'Tipo de documento fiscal es requerido',
            'fiscal_document_number' => 'Número de documento fiscal es requerido',
            'fiscal_country' => 'País es requerido',
            'fiscal_department' => 'Departamento/Estado es requerido',
            'fiscal_city' => 'Ciudad es requerida',
            'fiscal_address' => 'Dirección fiscal es requerida',
            'tax_regime' => 'Régimen tributario es requerido'
        ];

        foreach ($requiredFields as $field => $message) {
            if (empty($fiscalData[$field])) {
                $errors[$field] = $message;
            }
        }

        // Validate fiscal document
        if (!empty($fiscalData['fiscal_document_type']) && !empty($fiscalData['fiscal_document_number'])) {
            $documentValidation = $this->validateFiscalDocument(
                $fiscalData['fiscal_document_type'],
                $fiscalData['fiscal_document_number'],
                $fiscalData['fiscal_country'] ?? null
            );

            if (!$documentValidation['isValid']) {
                $errors['fiscal_document_number'] = $documentValidation['message'];
            }
        }

        // Validate tax regime
        if (!empty($fiscalData['tax_regime']) && !empty($fiscalData['fiscal_country'])) {
            if (!$this->validateTaxRegime($fiscalData['tax_regime'], $fiscalData['fiscal_country'])) {
                $errors['tax_regime'] = 'Régimen tributario no válido para el país seleccionado';
            }
        }

        // Validate address length
        if (!empty($fiscalData['fiscal_address']) && strlen($fiscalData['fiscal_address']) < 10) {
            $errors['fiscal_address'] = 'La dirección debe ser más específica (mínimo 10 caracteres)';
        }

        // Validate compliance checkboxes
        $complianceFields = [
            'tax_responsibility_declaration' => 'Debe aceptar la declaración de responsabilidad fiscal',
            'fiscal_data_processing_consent' => 'Debe autorizar el tratamiento de datos fiscales'
        ];

        // Legal representative declaration required for companies
        if (in_array($fiscalData['fiscal_document_type'] ?? '', ['nit', 'rut'])) {
            $complianceFields['legal_representative_declaration'] = 'Debe aceptar la declaración de representación legal';
        }

        foreach ($complianceFields as $field => $message) {
            if (empty($fiscalData[$field]) || $fiscalData[$field] !== true) {
                $errors[$field] = $message;
            }
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Información fiscal válida' : 'Por favor corrija los errores indicados'
        ];
    }

    /**
     * Clean document number based on type
     */
    private function cleanDocumentNumber(string $documentNumber, string $documentType): string
    {
        switch ($documentType) {
            case 'rfc':
                return strtoupper(preg_replace('/[^A-ZÑ&0-9]/', '', $documentNumber));
            case 'rut':
                return preg_replace('/[^0-9kK]/', '', $documentNumber);
            default:
                return preg_replace('/[^0-9]/', '', $documentNumber);
        }
    }

    /**
     * Generate document suggestions
     */
    private function generateDocumentSuggestions(string $documentType, string $documentNumber): array
    {
        $suggestions = [];
        $cleanNumber = $this->cleanDocumentNumber($documentNumber, $documentType);

        switch ($documentType) {
            case 'nit':
                if (strlen($cleanNumber) === 9) {
                    $suggestions[] = "¿Quizás quisiste decir: {$cleanNumber}-X? (falta el dígito de verificación)";
                }
                break;

            case 'rut':
                if (strlen($cleanNumber) === 8) {
                    $suggestions[] = "¿Quizás quisiste decir: {$cleanNumber}-X? (falta el dígito verificador)";
                }
                break;

            case 'rfc':
                if (strlen($documentNumber) < 13) {
                    $suggestions[] = 'El RFC debe tener 13 caracteres (4 letras + 6 números + 3 caracteres)';
                }
                break;
        }

        return $suggestions;
    }

    /**
     * Get document type requirements by country
     */
    public function getDocumentTypesByCountry(string $country): array
    {
        $documentTypes = [
            'Colombia' => [
                'nit' => 'NIT (Número de Identificación Tributaria)',
                'cedula' => 'Cédula de Ciudadanía'
            ],
            'México' => [
                'rfc' => 'RFC (Registro Federal de Contribuyentes)',
                'cedula' => 'Cédula de Ciudadanía'
            ],
            'Chile' => [
                'rut' => 'RUT (Registro Único Tributario)',
                'cedula' => 'Cédula de Ciudadanía'
            ],
            'Argentina' => [
                'cedula' => 'Cédula de Ciudadanía',
                'nit' => 'CUIT (Código Único de Identificación Tributaria)'
            ]
        ];

        return $documentTypes[$country] ?? [
            'cedula' => 'Cédula de Ciudadanía',
            'nit' => 'Número de Identificación Tributaria'
        ];
    }
}