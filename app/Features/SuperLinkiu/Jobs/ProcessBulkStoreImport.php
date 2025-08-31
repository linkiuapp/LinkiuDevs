<?php

namespace App\Features\SuperLinkiu\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use App\Core\Providers\RouteServiceProvider;
use App\Features\SuperLinkiu\Events\BulkImportProgressUpdated;
use App\Features\SuperLinkiu\Events\BulkImportCompleted;
use App\Features\SuperLinkiu\Models\BulkImportLog;

class ProcessBulkStoreImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $batchId;
    protected array $fileData;
    protected array $columnMapping;
    protected int $userId;

    public function __construct(string $batchId, array $fileData, array $columnMapping, int $userId)
    {
        $this->batchId = $batchId;
        $this->fileData = $fileData;
        $this->columnMapping = $columnMapping;
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info('Starting bulk store import', [
            'batch_id' => $this->batchId,
            'total_rows' => count($this->fileData['rows']),
            'user_id' => $this->userId
        ]);

        // Create audit log entry
        $importLog = BulkImportLog::createForImport($this->batchId, [
            'user_id' => $this->userId,
            'total_rows' => count($this->fileData['rows']),
            'column_mapping' => $this->columnMapping
        ]);

        $totalRows = count($this->fileData['rows']);
        $processed = 0;
        $successCount = 0;
        $errorCount = 0;
        $createdStores = [];
        $errors = [];

        // Update initial status
        $this->updateBatchStatus([
            'status' => 'processing',
            'total' => $totalRows,
            'processed' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'message' => 'Iniciando procesamiento...',
            'details' => "Procesando {$totalRows} registros"
        ]);

        foreach ($this->fileData['rows'] as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header
            
            try {
                $mappedData = $this->mapRowData($row, $this->columnMapping);
                $store = $this->createStoreFromData($mappedData, $rowNumber);
                
                if ($store) {
                    $successCount++;
                    $createdStores[] = [
                        'id' => $store->id,
                        'name' => $store->name,
                        'slug' => $store->slug,
                        'admin_email' => $mappedData['admin_email'],
                        'plan_name' => $store->plan->name ?? 'N/A'
                    ];
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                    'data' => $mappedData ?? $row
                ];
                
                Log::error('Error processing bulk import row', [
                    'batch_id' => $this->batchId,
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $mappedData ?? $row
                ]);
            }
            
            $processed++;
            
            // Update progress every 10 rows or on last row
            if ($processed % 10 === 0 || $processed === $totalRows) {
                $progressData = [
                    'status' => 'processing',
                    'total' => $totalRows,
                    'processed' => $processed,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'message' => "Procesando fila {$processed} de {$totalRows}",
                    'details' => "Exitosas: {$successCount}, Errores: {$errorCount}"
                ];
                
                $this->updateBatchStatus($progressData);
                
                // Update audit log
                $importLog->updateProgress($progressData);
                
                // Broadcast progress update via WebSocket
                broadcast(new BulkImportProgressUpdated($this->batchId, $progressData, $this->userId));
            }
        }

        // Final status update
        $finalStatus = $errorCount === 0 ? 'completed' : ($successCount > 0 ? 'completed_with_errors' : 'failed');
        
        $this->updateBatchStatus([
            'status' => $finalStatus,
            'total' => $totalRows,
            'processed' => $processed,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'message' => $this->getFinalMessage($successCount, $errorCount),
            'details' => "Procesamiento completado"
        ]);

        // Store final results
        $finalResults = [
            'batch_id' => $this->batchId,
            'total_processed' => $processed,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'created_stores' => $createdStores,
            'errors' => $errors,
            'completed_at' => now(),
            'status' => $finalStatus,
            'message' => $this->getFinalMessage($successCount, $errorCount)
        ];
        
        $this->storeResults($finalResults);
        
        // Complete audit log
        $importLog->complete($finalResults);
        
        // Broadcast completion event via WebSocket
        broadcast(new BulkImportCompleted($this->batchId, $finalResults, $this->userId));

        Log::info('Bulk store import completed', [
            'batch_id' => $this->batchId,
            'total_processed' => $processed,
            'success_count' => $successCount,
            'error_count' => $errorCount
        ]);
    }

    private function mapRowData(array $row, array $columnMapping): array
    {
        $mappedData = [];
        $columns = $this->fileData['columns'];
        
        foreach ($columnMapping as $sourceColumn => $targetField) {
            if ($targetField && ($columnIndex = array_search($sourceColumn, $columns)) !== false) {
                $value = $row[$columnIndex] ?? '';
                if (!empty($value)) {
                    $mappedData[$targetField] = trim($value);
                }
            }
        }

        return $mappedData;
    }

    private function createStoreFromData(array $data, int $rowNumber): ?Store
    {
        DB::beginTransaction();
        
        try {
            // Validate required fields
            $this->validateRequiredFields($data, $rowNumber);
            
            // Prepare store data
            $storeData = $this->prepareStoreData($data);
            
            // Prepare owner data
            $ownerData = $this->prepareOwnerData($data);
            
            // Create store
            $store = Store::create($storeData);
            
            // Create store admin
            $admin = User::create([
                'name' => $ownerData['name'],
                'email' => $ownerData['email'],
                'password' => bcrypt($ownerData['password']),
                'role' => 'store_admin',
                'store_id' => $store->id,
            ]);

            DB::commit();
            
            return $store;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateRequiredFields(array $data, int $rowNumber): void
    {
        $required = ['owner_name', 'admin_email', 'name', 'plan_id'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Campo requerido faltante: {$field}");
            }
        }

        // Validate email uniqueness
        if (User::where('email', $data['admin_email'])->exists()) {
            throw new \Exception("El email {$data['admin_email']} ya está en uso");
        }

        // Validate plan exists
        if (!Plan::find($data['plan_id'])) {
            throw new \Exception("Plan ID {$data['plan_id']} no existe");
        }
    }

    private function prepareStoreData(array $data): array
    {
        $plan = Plan::find($data['plan_id']);
        
        $storeData = [
            'name' => $data['name'],
            'plan_id' => $data['plan_id'],
            'status' => 'active',
            'verified' => false,
        ];

        // Generate or use provided slug
        if (!empty($data['slug'])) {
            $storeData['slug'] = $this->sanitizeSlug($data['slug']);
        } else {
            $storeData['slug'] = $this->generateSlugFromName($data['name']);
        }

        // Ensure slug is unique
        $storeData['slug'] = $this->ensureUniqueSlug($storeData['slug']);

        // Optional fields
        $optionalFields = [
            'email', 'phone', 'description', 'document_type', 'document_number',
            'country', 'department', 'city', 'address', 'meta_title', 
            'meta_description', 'meta_keywords'
        ];

        foreach ($optionalFields as $field) {
            if (!empty($data[$field])) {
                $storeData[$field] = $data[$field];
            }
        }

        return $storeData;
    }

    private function prepareOwnerData(array $data): array
    {
        return [
            'name' => $data['owner_name'],
            'email' => $data['admin_email'],
            'password' => $this->generateSecurePassword(),
            'document_type' => $data['owner_document_type'] ?? 'cedula',
            'document_number' => $data['owner_document_number'] ?? null,
            'country' => $data['owner_country'] ?? 'Colombia',
            'department' => $data['owner_department'] ?? null,
            'city' => $data['owner_city'] ?? null,
        ];
    }

    private function generateSlugFromName(string $name): string
    {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim($name));
        
        // Remove accents
        $accents = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        $slug = strtr($slug, $accents);
        
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove hyphens from start and end
        $slug = trim($slug, '-');
        
        // If empty, generate a random slug
        if (empty($slug)) {
            $slug = 'tienda-' . Str::random(8);
        }

        return $slug;
    }

    private function sanitizeSlug(string $slug): string
    {
        return $this->generateSlugFromName($slug);
    }

    private function ensureUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (Store::where('slug', $slug)->exists() || RouteServiceProvider::isReservedSlug($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateSecurePassword(): string
    {
        // Generate a secure but memorable password
        $adjectives = ['Quick', 'Bright', 'Smart', 'Fast', 'Cool', 'Nice', 'Good', 'Best'];
        $nouns = ['Store', 'Shop', 'Market', 'Plaza', 'Center', 'Point', 'Zone', 'Space'];
        
        $adjective = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];
        $number = rand(100, 999);
        
        return $adjective . $noun . $number . '!';
    }

    private function updateBatchStatus(array $status): void
    {
        cache()->put("bulk_import_batch_{$this->batchId}", $status, 3600);
    }

    private function storeResults(array $results): void
    {
        cache()->put("bulk_import_results_{$this->batchId}", $results, 86400); // 24 hours
    }

    private function getFinalMessage(int $successCount, int $errorCount): string
    {
        if ($errorCount === 0) {
            return "Importación completada exitosamente. {$successCount} tiendas creadas.";
        } elseif ($successCount > 0) {
            return "Importación completada con errores. {$successCount} tiendas creadas, {$errorCount} errores.";
        } else {
            return "Importación falló. {$errorCount} errores encontrados.";
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Bulk import job failed', [
            'batch_id' => $this->batchId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->updateBatchStatus([
            'status' => 'failed',
            'message' => 'Error crítico durante el procesamiento',
            'details' => $exception->getMessage(),
            'error' => true
        ]);
    }
}