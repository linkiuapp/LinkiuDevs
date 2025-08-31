<?php

namespace App\Features\SuperLinkiu\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use App\Features\SuperLinkiu\Jobs\ProcessBulkStoreImport;

/**
 * Bulk Import Batch Manager
 * Manages queue-based batch processing for bulk store imports
 * Requirements: 7.4, 7.5
 */
class BulkImportBatchManager
{
    /**
     * Create and queue a new bulk import batch
     */
    public function createBatch(array $fileData, array $columnMapping, int $userId): string
    {
        $batchId = Str::uuid();
        
        // Initialize batch status in cache
        $initialStatus = [
            'batch_id' => $batchId,
            'status' => 'queued',
            'total' => count($fileData['rows']),
            'processed' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'message' => 'Importación en cola...',
            'details' => 'Esperando procesamiento',
            'created_at' => now(),
            'user_id' => $userId,
            'queue_name' => 'bulk-import'
        ];
        
        cache()->put("bulk_import_batch_{$batchId}", $initialStatus, 3600); // 1 hour
        
        // Dispatch job to queue
        ProcessBulkStoreImport::dispatch($batchId, $fileData, $columnMapping, $userId)
            ->onQueue('bulk-import')
            ->delay(now()->addSeconds(2)); // Small delay to ensure UI is ready
        
        Log::info('Bulk import batch created and queued', [
            'batch_id' => $batchId,
            'total_rows' => count($fileData['rows']),
            'user_id' => $userId
        ]);
        
        return $batchId;
    }

    /**
     * Get batch status and progress
     */
    public function getBatchStatus(string $batchId): ?array
    {
        return cache()->get("bulk_import_batch_{$batchId}");
    }

    /**
     * Get batch results
     */
    public function getBatchResults(string $batchId): ?array
    {
        return cache()->get("bulk_import_results_{$batchId}");
    }

    /**
     * Cancel a running batch
     */
    public function cancelBatch(string $batchId): bool
    {
        try {
            $status = $this->getBatchStatus($batchId);
            
            if (!$status) {
                return false;
            }
            
            // Mark as cancelled
            $status['status'] = 'cancelled';
            $status['message'] = 'Importación cancelada por el usuario';
            $status['cancelled_at'] = now();
            
            cache()->put("bulk_import_batch_{$batchId}", $status, 3600);
            
            Log::info('Bulk import batch cancelled', ['batch_id' => $batchId]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error cancelling bulk import batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Retry a failed batch
     */
    public function retryBatch(string $batchId): ?string
    {
        try {
            $originalStatus = $this->getBatchStatus($batchId);
            $originalResults = $this->getBatchResults($batchId);
            
            if (!$originalStatus || !$originalResults) {
                return null;
            }
            
            // Create new batch ID for retry
            $newBatchId = Str::uuid();
            
            // Get original file data from session or cache
            $fileData = session('bulk_import_data');
            if (!$fileData) {
                throw new \Exception('Original file data not found');
            }
            
            // Create new batch with same parameters
            $this->createBatch($fileData, [], $originalStatus['user_id']);
            
            Log::info('Bulk import batch retried', [
                'original_batch_id' => $batchId,
                'new_batch_id' => $newBatchId
            ]);
            
            return $newBatchId;
            
        } catch (\Exception $e) {
            Log::error('Error retrying bulk import batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Get all batches for a user
     */
    public function getUserBatches(int $userId, int $limit = 10): array
    {
        // This would typically query a database table
        // For now, we'll return a placeholder since we're using cache
        
        return [
            'batches' => [],
            'total' => 0
        ];
    }

    /**
     * Clean up old batch data
     */
    public function cleanupOldBatches(int $daysOld = 7): int
    {
        $cleaned = 0;
        
        try {
            // This would clean up cache entries older than specified days
            // Implementation depends on cache driver
            
            Log::info('Cleaning up old bulk import batches', [
                'days_old' => $daysOld,
                'cleaned_count' => $cleaned
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error cleaning up old batches', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $cleaned;
    }

    /**
     * Get batch statistics
     */
    public function getBatchStatistics(string $batchId): array
    {
        $status = $this->getBatchStatus($batchId);
        $results = $this->getBatchResults($batchId);
        
        if (!$status) {
            return [];
        }
        
        $stats = [
            'batch_id' => $batchId,
            'status' => $status['status'],
            'total_rows' => $status['total'],
            'processed_rows' => $status['processed'],
            'success_count' => $status['success_count'],
            'error_count' => $status['error_count'],
            'progress_percentage' => $status['total'] > 0 ? round(($status['processed'] / $status['total']) * 100, 2) : 0,
            'created_at' => $status['created_at'],
            'is_completed' => in_array($status['status'], ['completed', 'completed_with_errors', 'failed']),
            'has_errors' => $status['error_count'] > 0
        ];
        
        if ($results) {
            $stats['completed_at'] = $results['completed_at'] ?? null;
            $stats['created_stores_count'] = count($results['created_stores'] ?? []);
            $stats['errors_count'] = count($results['errors'] ?? []);
        }
        
        return $stats;
    }

    /**
     * Monitor queue health
     */
    public function getQueueHealth(): array
    {
        try {
            // Get queue size and failed jobs count
            $queueSize = Queue::size('bulk-import');
            $failedJobs = \DB::table('failed_jobs')
                ->where('payload', 'like', '%ProcessBulkStoreImport%')
                ->count();
            
            return [
                'queue_name' => 'bulk-import',
                'pending_jobs' => $queueSize,
                'failed_jobs' => $failedJobs,
                'is_healthy' => $queueSize < 100 && $failedJobs < 10, // Arbitrary thresholds
                'last_checked' => now()
            ];
            
        } catch (\Exception $e) {
            Log::error('Error checking queue health', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'queue_name' => 'bulk-import',
                'is_healthy' => false,
                'error' => $e->getMessage(),
                'last_checked' => now()
            ];
        }
    }

    /**
     * Pause batch processing
     */
    public function pauseBatch(string $batchId): bool
    {
        try {
            $status = $this->getBatchStatus($batchId);
            
            if (!$status || $status['status'] !== 'processing') {
                return false;
            }
            
            $status['status'] = 'paused';
            $status['message'] = 'Importación pausada';
            $status['paused_at'] = now();
            
            cache()->put("bulk_import_batch_{$batchId}", $status, 3600);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error pausing batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Resume paused batch processing
     */
    public function resumeBatch(string $batchId): bool
    {
        try {
            $status = $this->getBatchStatus($batchId);
            
            if (!$status || $status['status'] !== 'paused') {
                return false;
            }
            
            $status['status'] = 'processing';
            $status['message'] = 'Importación reanudada';
            $status['resumed_at'] = now();
            
            cache()->put("bulk_import_batch_{$batchId}", $status, 3600);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error resuming batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}