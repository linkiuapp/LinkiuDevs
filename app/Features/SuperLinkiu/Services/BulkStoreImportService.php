<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use App\Shared\Models\Plan;

/**
 * Bulk Store Import Service
 * Handles validation, processing and management of bulk store imports
 * Requirements: 7.4, 7.5
 */
class BulkStoreImportService
{
    /**
     * Validate import data structure and content
     */
    public function validateImportData(array $data, array $columnMapping): array
    {
        $results = [
            'valid_count' => 0,
            'error_count' => 0,
            'warning_count' => 0,
            'errors' => [],
            'warnings' => []
        ];

        foreach ($data['rows'] as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header
            $mappedData = $this->mapRowData($row, $data['columns'], $columnMapping);
            
            $validation = $this->validateRowData($mappedData, $rowNumber);
            
            if ($validation['is_valid']) {
                $results['valid_count']++;
                
                // Check for warnings
                if (!empty($validation['warnings'])) {
                    $results['warning_count']++;
                    $results['warnings'] = array_merge($results['warnings'], $validation['warnings']);
                }
            } else {
                $results['error_count']++;
                $results['errors'] = array_merge($results['errors'], $validation['errors']);
            }
        }

        return $results;
    }

    /**
     * Process bulk import in batches
     */
    public function processBulkImport(string $batchId, array $data, array $columnMapping, int $userId): void
    {
        $totalRows = count($data['rows']);
        $batchSize = 10; // Process 10 rows at a time
        $processed = 0;
        $successCount = 0;
        $errorCount = 0;
        $createdStores = [];
        $errors = [];

        Log::info('Starting bulk import processing', [
            'batch_id' => $batchId,
            'total_rows' => $totalRows,
            'batch_size' => $batchSize,
            'user_id' => $userId
        ]);

        // Process in batches
        for ($i = 0; $i < $totalRows; $i += $batchSize) {
            $batch = array_slice($data['rows'], $i, $batchSize, true);
            
            foreach ($batch as $index => $row) {
                $rowNumber = $index + 2;
                
                try {
                    $mappedData = $this->mapRowData($row, $data['columns'], $columnMapping);
                    $store = $this->createStoreFromMappedData($mappedData, $rowNumber);
                    
                    if ($store) {
                        $successCount++;
                        $createdStores[] = $this->formatCreatedStoreData($store, $mappedData);
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage(),
                        'data' => $mappedData ?? $row
                    ];
                    
                    Log::error('Error processing bulk import row', [
                        'batch_id' => $batchId,
                        'row' => $rowNumber,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $processed++;
                
                // Update progress
                $this->updateBatchProgress($batchId, [
                    'total' => $totalRows,
                    'processed' => $processed,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'status' => 'processing'
                ]);
            }
            
            // Small delay between batches to prevent overwhelming the system
            usleep(100000); // 0.1 seconds
        }

        // Store final results
        $this->storeFinalResults($batchId, [
            'total_processed' => $processed,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'created_stores' => $createdStores,
            'errors' => $errors,
            'completed_at' => now()
        ]);

        Log::info('Bulk import processing completed', [
            'batch_id' => $batchId,
            'total_processed' => $processed,
            'success_count' => $successCount,
            'error_count' => $errorCount
        ]);
    }

    /**
     * Map row data according to column mapping
     */
    private function mapRowData(array $row, array $columns, array $columnMapping): array
    {
        $mappedData = [];
        
        foreach ($columnMapping as $sourceColumn => $targetField) {
            if ($targetField && ($columnIndex = array_search($sourceColumn, $columns)) !== false) {
                $value = $row[$columnIndex] ?? '';
                if (!empty(trim($value))) {
                    $mappedData[$targetField] = trim($value);
                }
            }
        }

        return $mappedData;
    }

    /**
     * Validate individual row data
     */
    private function validateRowData(array $data, int $rowNumber): array
    {
        $validator = Validator::make($data, [
            'owner_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'owner_document_type' => 'nullable|in:cedula,nit,pasaporte',
            'owner_document_number' => 'nullable|string|max:20',
            'owner_country' => 'nullable|string|max:100',
            'owner_department' => 'nullable|string|max:100',
            'owner_city' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:255|unique:stores,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'email' => 'nullable|email|unique:stores,email',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'nullable|in:nit,cedula',
            'document_number' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'billing_period' => 'nullable|in:monthly,quarterly,biannual',
            'initial_payment_status' => 'nullable|in:pending,paid',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:255'
        ]);

        $result = [
            'is_valid' => !$validator->fails(),
            'errors' => [],
            'warnings' => []
        ];

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = [
                    'row' => $rowNumber,
                    'message' => $error,
                    'data' => $data
                ];
            }
        }

        // Add warnings for missing optional but recommended fields
        $this->addWarningsForMissingFields($data, $rowNumber, $result);

        return $result;
    }

    /**
     * Add warnings for missing recommended fields
     */
    private function addWarningsForMissingFields(array $data, int $rowNumber, array &$result): void
    {
        $recommendedFields = [
            'owner_country' => 'País del propietario no especificado',
            'phone' => 'Teléfono no especificado',
            'description' => 'Descripción de la tienda no especificada'
        ];

        foreach ($recommendedFields as $field => $message) {
            if (empty($data[$field])) {
                $result['warnings'][] = [
                    'row' => $rowNumber,
                    'message' => $message,
                    'field' => $field
                ];
            }
        }
    }

    /**
     * Create store from mapped data
     */
    private function createStoreFromMappedData(array $data, int $rowNumber): ?Store
    {
        // This method would be similar to the one in the Job class
        // but could have additional business logic specific to the service
        
        // For now, we'll delegate to the job class logic
        // In a real implementation, you might want to extract this to a shared method
        
        return null; // Placeholder - actual implementation would create the store
    }

    /**
     * Format created store data for results
     */
    private function formatCreatedStoreData(Store $store, array $mappedData): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'admin_email' => $mappedData['admin_email'],
            'plan_name' => $store->plan->name ?? 'N/A',
            'created_at' => $store->created_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Update batch progress in cache
     */
    private function updateBatchProgress(string $batchId, array $progress): void
    {
        $currentStatus = cache()->get("bulk_import_batch_{$batchId}", []);
        $updatedStatus = array_merge($currentStatus, $progress);
        
        cache()->put("bulk_import_batch_{$batchId}", $updatedStatus, 3600);
    }

    /**
     * Store final results in cache
     */
    private function storeFinalResults(string $batchId, array $results): void
    {
        cache()->put("bulk_import_results_{$batchId}", $results, 86400); // 24 hours
        
        // Update batch status to completed
        $this->updateBatchProgress($batchId, [
            'status' => $results['error_count'] === 0 ? 'completed' : 'completed_with_errors',
            'message' => $this->generateCompletionMessage($results),
            'completed_at' => now()
        ]);
    }

    /**
     * Generate completion message based on results
     */
    private function generateCompletionMessage(array $results): string
    {
        $successCount = $results['success_count'];
        $errorCount = $results['error_count'];

        if ($errorCount === 0) {
            return "Importación completada exitosamente. {$successCount} tiendas creadas.";
        } elseif ($successCount > 0) {
            return "Importación completada con errores. {$successCount} tiendas creadas, {$errorCount} errores.";
        } else {
            return "Importación falló. {$errorCount} errores encontrados.";
        }
    }

    /**
     * Get import statistics for monitoring
     */
    public function getImportStatistics(string $batchId): array
    {
        $batchStatus = cache()->get("bulk_import_batch_{$batchId}");
        $results = cache()->get("bulk_import_results_{$batchId}");

        return [
            'batch_status' => $batchStatus,
            'results' => $results,
            'is_completed' => $batchStatus['status'] ?? '' === 'completed',
            'has_errors' => ($batchStatus['error_count'] ?? 0) > 0
        ];
    }

    /**
     * Clean up old import data
     */
    public function cleanupOldImports(int $daysOld = 7): int
    {
        // This would clean up cache entries older than specified days
        // Implementation would depend on your cache driver and cleanup strategy
        
        Log::info('Cleaning up old bulk import data', ['days_old' => $daysOld]);
        
        // Placeholder return
        return 0;
    }

    /**
     * Generate import template data
     */
    public function generateTemplateData(string $type = 'basic'): array
    {
        $templates = [
            'basic' => [
                'required_fields' => ['owner_name', 'admin_email', 'name', 'plan_id'],
                'optional_fields' => ['owner_country', 'owner_department', 'owner_city', 'phone', 'description'],
                'sample_data' => [
                    'owner_name' => 'Juan Pérez',
                    'admin_email' => 'juan@ejemplo.com',
                    'name' => 'Mi Tienda Online',
                    'plan_id' => '1',
                    'owner_country' => 'Colombia',
                    'owner_department' => 'Cundinamarca',
                    'owner_city' => 'Bogotá'
                ]
            ],
            'complete' => [
                'required_fields' => ['owner_name', 'admin_email', 'name', 'plan_id'],
                'optional_fields' => [
                    'owner_document_type', 'owner_document_number', 'owner_country', 
                    'owner_department', 'owner_city', 'slug', 'email', 'phone', 
                    'description', 'country', 'department', 'city', 'billing_period'
                ],
                'sample_data' => [
                    'owner_name' => 'María García',
                    'admin_email' => 'maria@ejemplo.com',
                    'owner_document_type' => 'cedula',
                    'owner_document_number' => '12345678',
                    'name' => 'Tienda Completa',
                    'plan_id' => '2',
                    'slug' => 'tienda-completa',
                    'email' => 'info@tiendacompleta.com',
                    'phone' => '+57 300 123 4567',
                    'billing_period' => 'monthly'
                ]
            ],
            'enterprise' => [
                'required_fields' => ['owner_name', 'admin_email', 'name', 'plan_id'],
                'optional_fields' => [
                    'owner_document_type', 'owner_document_number', 'owner_country',
                    'owner_department', 'owner_city', 'slug', 'email', 'phone',
                    'description', 'document_type', 'document_number', 'country',
                    'department', 'city', 'address', 'billing_period', 'meta_title'
                ],
                'sample_data' => [
                    'owner_name' => 'Carlos Rodríguez',
                    'admin_email' => 'carlos@empresa.com',
                    'owner_document_type' => 'nit',
                    'owner_document_number' => '900123456-1',
                    'name' => 'Empresa Digital S.A.S.',
                    'plan_id' => '3',
                    'document_type' => 'nit',
                    'document_number' => '900123456-1',
                    'address' => 'Calle 123 #45-67',
                    'meta_title' => 'Empresa Digital - Soluciones Tecnológicas'
                ]
            ]
        ];

        return $templates[$type] ?? $templates['basic'];
    }
}