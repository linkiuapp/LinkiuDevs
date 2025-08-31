<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Store Template Service
 * 
 * Manages store creation templates, configurations, and form field mappings
 * Requirements: 3.2, 3.4
 */
class StoreTemplateService
{
    /**
     * Cache key for templates
     */
    private const CACHE_KEY = 'store_templates';
    
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Available store templates
     */
    private array $templates = [];

    /**
     * Template validation rules
     */
    private array $validationRules = [
        'id' => 'required|string|max:50',
        'name' => 'required|string|max:100',
        'description' => 'required|string|max:500',
        'icon' => 'required|string|max:50',
        'capabilities' => 'required|array',
        'steps' => 'required|array|min:1',
        'fieldCount' => 'integer|min:1',
        'estimatedTime' => 'string|max:20'
    ];

    public function __construct()
    {
        $this->loadTemplates();
    }

    /**
     * Get all available templates
     */
    public function getAllTemplates(): Collection
    {
        return collect($this->templates);
    }

    /**
     * Get template by ID
     */
    public function getTemplate(string $templateId): ?array
    {
        return $this->templates[$templateId] ?? null;
    }

    /**
     * Get template configuration for form generation
     */
    public function getTemplateConfig(string $templateId): array
    {
        $template = $this->getTemplate($templateId);
        
        if (!$template) {
            throw new InvalidArgumentException("Template '{$templateId}' not found");
        }

        return [
            'id' => $template['id'],
            'name' => $template['name'],
            'steps' => $this->processTemplateSteps($template['steps']),
            'capabilities' => $template['capabilities'],
            'defaultValues' => $this->getTemplateDefaults($templateId),
            'validationRules' => $this->getTemplateValidationRules($templateId),
            'fieldMapping' => $this->getTemplateFieldMapping($templateId)
        ];
    }

    /**
     * Get template-specific default values
     */
    public function getTemplateDefaults(string $templateId): array
    {
        $defaults = [
            'basic' => [
                'owner_country' => 'Colombia',
                'status' => 'active',
                'plan_type' => 'basic'
            ],
            'complete' => [
                'owner_country' => 'Colombia',
                'status' => 'active',
                'plan_type' => 'standard',
                'seo_enabled' => true
            ],
            'enterprise' => [
                'owner_country' => 'Colombia',
                'status' => 'active',
                'plan_type' => 'enterprise',
                'seo_enabled' => true,
                'fiscal_required' => true,
                'compliance_enabled' => true
            ]
        ];

        return $defaults[$templateId] ?? [];
    }

    /**
     * Get template-specific validation rules
     */
    public function getTemplateValidationRules(string $templateId): array
    {
        $baseRules = [
            'owner_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug|regex:/^[a-z0-9-]+$/',
            'plan_id' => 'required|exists:plans,id'
        ];

        $templateRules = [
            'basic' => [],
            'complete' => [
                'owner_document_type' => 'required|in:cedula,nit,pasaporte',
                'owner_document_number' => 'required|string|max:20',
                'owner_country' => 'required|string|max:100',
                'email' => 'nullable|email|unique:stores,email',
                'phone' => 'nullable|string|max:20',
                'description' => 'nullable|string|max:1000'
            ],
            'enterprise' => [
                'owner_document_type' => 'required|in:cedula,nit,pasaporte',
                'owner_document_number' => 'required|string|max:20',
                'owner_country' => 'required|string|max:100',
                'owner_department' => 'required|string|max:100',
                'owner_city' => 'required|string|max:100',
                'email' => 'required|email|unique:stores,email',
                'phone' => 'required|string|max:20',
                'description' => 'nullable|string|max:1000',
                'fiscal_document_type' => 'required|in:nit,cedula',
                'fiscal_document_number' => 'required|string|max:20',
                'fiscal_address' => 'required|string|max:500',
                'tax_regime' => 'required|string|max:100'
            ]
        ];

        return array_merge($baseRules, $templateRules[$templateId] ?? []);
    }

    /**
     * Get template field mapping for form generation
     */
    public function getTemplateFieldMapping(string $templateId): array
    {
        $template = $this->getTemplate($templateId);
        
        if (!$template) {
            return [];
        }

        $mapping = [];
        
        foreach ($template['steps'] as $step) {
            foreach ($step['fields'] as $field) {
                $mapping[$field['name']] = [
                    'step' => $step['id'],
                    'label' => $field['label'],
                    'type' => $field['type'] ?? 'text',
                    'required' => $field['required'] ?? false,
                    'validation' => $field['validation'] ?? [],
                    'placeholder' => $field['placeholder'] ?? null,
                    'options' => $field['options'] ?? null,
                    'dependsOn' => $field['dependsOn'] ?? null
                ];
            }
        }

        return $mapping;
    }

    /**
     * Validate template configuration
     */
    public function validateTemplate(array $template): bool
    {
        $validator = Validator::make($template, $this->validationRules);
        
        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Invalid template configuration: ' . implode(', ', $validator->errors()->all())
            );
        }

        // Validate steps structure
        foreach ($template['steps'] as $step) {
            $this->validateTemplateStep($step);
        }

        return true;
    }

    /**
     * Validate template step configuration
     */
    private function validateTemplateStep(array $step): bool
    {
        $stepRules = [
            'id' => 'required|string|max:50',
            'title' => 'required|string|max:100',
            'order' => 'required|integer|min:1',
            'isOptional' => 'boolean',
            'fields' => 'required|array|min:1'
        ];

        $validator = Validator::make($step, $stepRules);
        
        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Invalid step configuration: ' . implode(', ', $validator->errors()->all())
            );
        }

        // Validate fields
        foreach ($step['fields'] as $field) {
            $this->validateTemplateField($field);
        }

        return true;
    }

    /**
     * Validate template field configuration
     */
    private function validateTemplateField(array $field): bool
    {
        $fieldRules = [
            'name' => 'required|string|max:50',
            'label' => 'required|string|max:100',
            'type' => 'string|in:text,email,password,select,textarea,number,tel,url',
            'required' => 'boolean'
        ];

        $validator = Validator::make($field, $fieldRules);
        
        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Invalid field configuration: ' . implode(', ', $validator->errors()->all())
            );
        }

        return true;
    }

    /**
     * Process template steps for form generation
     */
    private function processTemplateSteps(array $steps): array
    {
        return collect($steps)
            ->sortBy('order')
            ->map(function ($step) {
                return [
                    'id' => $step['id'],
                    'title' => $step['title'],
                    'order' => $step['order'],
                    'isOptional' => $step['isOptional'] ?? false,
                    'fields' => $this->processStepFields($step['fields']),
                    'dependencies' => $step['dependencies'] ?? []
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Process step fields for form generation
     */
    private function processStepFields(array $fields): array
    {
        return collect($fields)
            ->map(function ($field) {
                return [
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'] ?? 'text',
                    'required' => $field['required'] ?? false,
                    'placeholder' => $field['placeholder'] ?? null,
                    'validation' => $field['validation'] ?? [],
                    'options' => $field['options'] ?? null,
                    'dependsOn' => $field['dependsOn'] ?? null,
                    'autoComplete' => $field['autoComplete'] ?? null
                ];
            })
            ->toArray();
    }

    /**
     * Check if template has capability
     */
    public function hasCapability(string $templateId, string $capability): bool
    {
        $template = $this->getTemplate($templateId);
        
        if (!$template) {
            return false;
        }

        return in_array($capability, $template['capabilities'] ?? []);
    }

    /**
     * Get templates by capability
     */
    public function getTemplatesByCapability(string $capability): Collection
    {
        return $this->getAllTemplates()
            ->filter(function ($template) use ($capability) {
                return $this->hasCapability($template['id'], $capability);
            });
    }

    /**
     * Get recommended template
     */
    public function getRecommendedTemplate(): ?array
    {
        return $this->getAllTemplates()
            ->firstWhere('isRecommended', true);
    }

    /**
     * Load templates from configuration
     */
    private function loadTemplates(): void
    {
        $this->templates = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->getDefaultTemplates();
        });
    }

    /**
     * Get default template configurations
     */
    private function getDefaultTemplates(): array
    {
        return [
            'basic' => [
                'id' => 'basic',
                'name' => 'Tienda Básica',
                'description' => 'Configuración rápida con campos esenciales',
                'icon' => 'shop',
                'iconColor' => 'text-blue-500',
                'isRecommended' => true,
                'fieldCount' => 8,
                'estimatedTime' => '5 min',
                'features' => [
                    'Información básica del propietario',
                    'Configuración de tienda simple',
                    'Plan básico incluido',
                    'Configuración rápida'
                ],
                'capabilities' => ['basicInfo'],
                'steps' => [
                    [
                        'id' => 'owner-info',
                        'title' => 'Información del Propietario',
                        'order' => 1,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'owner_name', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
                            ['name' => 'admin_email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                            ['name' => 'admin_password', 'label' => 'Contraseña', 'type' => 'password', 'required' => true]
                        ]
                    ],
                    [
                        'id' => 'store-config',
                        'title' => 'Configuración de Tienda',
                        'order' => 2,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'name', 'label' => 'Nombre de la Tienda', 'type' => 'text', 'required' => true],
                            ['name' => 'slug', 'label' => 'URL', 'type' => 'text', 'required' => true],
                            ['name' => 'plan_id', 'label' => 'Plan', 'type' => 'select', 'required' => true]
                        ]
                    ]
                ]
            ],
            'complete' => [
                'id' => 'complete',
                'name' => 'Tienda Completa',
                'description' => 'Configuración completa con todas las opciones',
                'icon' => 'buildings',
                'iconColor' => 'text-green-500',
                'isRecommended' => false,
                'fieldCount' => 15,
                'estimatedTime' => '10 min',
                'features' => [
                    'Información completa del propietario',
                    'Configuración avanzada de tienda',
                    'Información fiscal opcional',
                    'Configuración SEO básica',
                    'Múltiples opciones de plan'
                ],
                'capabilities' => ['basicInfo', 'fiscalInfo', 'seoConfig'],
                'steps' => [
                    [
                        'id' => 'owner-info',
                        'title' => 'Información del Propietario',
                        'order' => 1,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'owner_name', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
                            ['name' => 'admin_email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                            ['name' => 'owner_document_type', 'label' => 'Tipo de Documento', 'type' => 'select', 'required' => true, 'options' => ['cedula' => 'Cédula', 'nit' => 'NIT', 'pasaporte' => 'Pasaporte']],
                            ['name' => 'owner_document_number', 'label' => 'Número de Documento', 'type' => 'text', 'required' => true],
                            ['name' => 'owner_country', 'label' => 'País', 'type' => 'text', 'required' => true],
                            ['name' => 'admin_password', 'label' => 'Contraseña', 'type' => 'password', 'required' => true]
                        ]
                    ],
                    [
                        'id' => 'store-config',
                        'title' => 'Configuración de Tienda',
                        'order' => 2,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'name', 'label' => 'Nombre de la Tienda', 'type' => 'text', 'required' => true],
                            ['name' => 'slug', 'label' => 'URL', 'type' => 'text', 'required' => true],
                            ['name' => 'email', 'label' => 'Email de Contacto', 'type' => 'email', 'required' => false],
                            ['name' => 'phone', 'label' => 'Teléfono', 'type' => 'tel', 'required' => false],
                            ['name' => 'description', 'label' => 'Descripción', 'type' => 'textarea', 'required' => false],
                            ['name' => 'plan_id', 'label' => 'Plan', 'type' => 'select', 'required' => true]
                        ]
                    ],
                    [
                        'id' => 'seo-config',
                        'title' => 'Configuración SEO',
                        'order' => 3,
                        'isOptional' => true,
                        'fields' => [
                            ['name' => 'meta_title', 'label' => 'Título SEO', 'type' => 'text', 'required' => false],
                            ['name' => 'meta_description', 'label' => 'Descripción SEO', 'type' => 'textarea', 'required' => false],
                            ['name' => 'meta_keywords', 'label' => 'Palabras Clave', 'type' => 'text', 'required' => false]
                        ]
                    ]
                ]
            ],
            'enterprise' => [
                'id' => 'enterprise',
                'name' => 'Tienda Empresarial',
                'description' => 'Enfoque en información fiscal y compliance',
                'icon' => 'case',
                'iconColor' => 'text-purple-500',
                'isRecommended' => false,
                'fieldCount' => 20,
                'estimatedTime' => '15 min',
                'features' => [
                    'Información fiscal completa',
                    'Configuración empresarial',
                    'Compliance y documentación',
                    'Configuración SEO avanzada',
                    'Opciones de dominio personalizado',
                    'Analytics integrado'
                ],
                'capabilities' => ['basicInfo', 'fiscalInfo', 'seoConfig', 'advancedConfig', 'customDomain', 'analytics'],
                'steps' => [
                    [
                        'id' => 'owner-info',
                        'title' => 'Información del Propietario',
                        'order' => 1,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'owner_name', 'label' => 'Nombre del Representante', 'type' => 'text', 'required' => true],
                            ['name' => 'admin_email', 'label' => 'Email Corporativo', 'type' => 'email', 'required' => true],
                            ['name' => 'owner_document_type', 'label' => 'Tipo de Documento', 'type' => 'select', 'required' => true, 'options' => ['cedula' => 'Cédula', 'nit' => 'NIT', 'pasaporte' => 'Pasaporte']],
                            ['name' => 'owner_document_number', 'label' => 'Número de Documento', 'type' => 'text', 'required' => true],
                            ['name' => 'owner_country', 'label' => 'País', 'type' => 'text', 'required' => true],
                            ['name' => 'owner_department', 'label' => 'Departamento', 'type' => 'text', 'required' => true],
                            ['name' => 'owner_city', 'label' => 'Ciudad', 'type' => 'text', 'required' => true],
                            ['name' => 'admin_password', 'label' => 'Contraseña', 'type' => 'password', 'required' => true]
                        ]
                    ],
                    [
                        'id' => 'store-config',
                        'title' => 'Configuración de Tienda',
                        'order' => 2,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'name', 'label' => 'Razón Social', 'type' => 'text', 'required' => true],
                            ['name' => 'slug', 'label' => 'URL Corporativa', 'type' => 'text', 'required' => true],
                            ['name' => 'email', 'label' => 'Email de Contacto', 'type' => 'email', 'required' => true],
                            ['name' => 'phone', 'label' => 'Teléfono Corporativo', 'type' => 'tel', 'required' => true],
                            ['name' => 'description', 'label' => 'Descripción Empresarial', 'type' => 'textarea', 'required' => false],
                            ['name' => 'plan_id', 'label' => 'Plan Empresarial', 'type' => 'select', 'required' => true]
                        ]
                    ],
                    [
                        'id' => 'fiscal-info',
                        'title' => 'Información Fiscal',
                        'order' => 3,
                        'isOptional' => false,
                        'fields' => [
                            ['name' => 'fiscal_document_type', 'label' => 'Tipo de Documento Fiscal', 'type' => 'select', 'required' => true, 'options' => ['nit' => 'NIT', 'cedula' => 'Cédula']],
                            ['name' => 'fiscal_document_number', 'label' => 'NIT/RUT', 'type' => 'text', 'required' => true],
                            ['name' => 'fiscal_address', 'label' => 'Dirección Fiscal', 'type' => 'textarea', 'required' => true],
                            ['name' => 'tax_regime', 'label' => 'Régimen Tributario', 'type' => 'select', 'required' => true, 'options' => ['simplificado' => 'Simplificado', 'comun' => 'Común', 'gran_contribuyente' => 'Gran Contribuyente']]
                        ]
                    ],
                    [
                        'id' => 'seo-config',
                        'title' => 'Configuración SEO',
                        'order' => 4,
                        'isOptional' => true,
                        'fields' => [
                            ['name' => 'meta_title', 'label' => 'Título SEO', 'type' => 'text', 'required' => false],
                            ['name' => 'meta_description', 'label' => 'Descripción SEO', 'type' => 'textarea', 'required' => false]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->loadTemplates();
    }

    /**
     * Add custom template
     */
    public function addTemplate(array $template): void
    {
        $this->validateTemplate($template);
        
        $this->templates[$template['id']] = $template;
        
        // Update cache
        Cache::put(self::CACHE_KEY, $this->templates, self::CACHE_TTL);
    }

    /**
     * Remove template
     */
    public function removeTemplate(string $templateId): bool
    {
        if (!isset($this->templates[$templateId])) {
            return false;
        }

        unset($this->templates[$templateId]);
        
        // Update cache
        Cache::put(self::CACHE_KEY, $this->templates, self::CACHE_TTL);
        
        return true;
    }
}