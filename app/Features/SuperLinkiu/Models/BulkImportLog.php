<?php

namespace App\Features\SuperLinkiu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Models\User;

/**
 * Bulk Import Log Model
 * Tracks all bulk import operations for auditing and reporting
 * Requirements: 7.6
 */
class BulkImportLog extends Model
{
    protected $fillable = [
        'batch_id',
        'user_id',
        'file_name',
        'file_size',
        'total_rows',
        'processed_rows',
        'success_count',
        'error_count',
        'status',
        'started_at',
        'completed_at',
        'error_details',
        'processing_time_seconds',
        'template_type',
        'column_mapping',
        'validation_errors',
        'created_stores_data',
        'queue_name',
        'job_attempts',
        'memory_usage_mb',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_details' => 'array',
        'column_mapping' => 'array',
        'validation_errors' => 'array',
        'created_stores_data' => 'array',
        'processing_time_seconds' => 'integer',
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'success_count' => 'integer',
        'error_count' => 'integer',
        'job_attempts' => 'integer',
        'memory_usage_mb' => 'float'
    ];

    /**
     * Get the user who initiated the import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful imports
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed')->where('error_count', 0);
    }

    /**
     * Scope for failed imports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for processing imports
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for recent imports
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        
        return round(($this->success_count / $this->total_rows) * 100, 2);
    }

    /**
     * Get processing duration in human readable format
     */
    public function getProcessingDurationAttribute(): string
    {
        if (!$this->started_at || !$this->completed_at) {
            return 'N/A';
        }
        
        $seconds = $this->processing_time_seconds;
        
        if ($seconds < 60) {
            return "{$seconds} segundos";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes} minutos";
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes === 0) {
            return '0 Bytes';
        }
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'completed' => $this->error_count > 0 ? 'bg-warning' : 'bg-success',
            'processing' => 'bg-info',
            'failed' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            'queued' => 'bg-light text-dark',
            default => 'bg-secondary'
        };
    }

    /**
     * Get status display text
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'completed' => $this->error_count > 0 ? 'Completada con errores' : 'Completada',
            'processing' => 'Procesando',
            'failed' => 'Fallida',
            'cancelled' => 'Cancelada',
            'queued' => 'En cola',
            default => ucfirst($this->status)
        };
    }

    /**
     * Check if import has errors
     */
    public function hasErrors(): bool
    {
        return $this->error_count > 0;
    }

    /**
     * Check if import is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    /**
     * Check if import is in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['processing', 'queued']);
    }

    /**
     * Get error summary
     */
    public function getErrorSummary(): array
    {
        if (!$this->error_details) {
            return [];
        }

        $errorTypes = [];
        foreach ($this->error_details as $error) {
            $type = $this->categorizeError($error['message'] ?? '');
            $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
        }

        return $errorTypes;
    }

    /**
     * Categorize error by message
     */
    private function categorizeError(string $message): string
    {
        if (str_contains($message, 'email')) {
            return 'Email duplicado';
        } elseif (str_contains($message, 'slug')) {
            return 'URL duplicada';
        } elseif (str_contains($message, 'required')) {
            return 'Campo requerido';
        } elseif (str_contains($message, 'plan')) {
            return 'Plan inválido';
        } else {
            return 'Otro error';
        }
    }

    /**
     * Create log entry for new import
     */
    public static function createForImport(string $batchId, array $data): self
    {
        return self::create([
            'batch_id' => $batchId,
            'user_id' => $data['user_id'],
            'file_name' => $data['file_name'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'total_rows' => $data['total_rows'] ?? 0,
            'status' => 'queued',
            'started_at' => now(),
            'template_type' => $data['template_type'] ?? 'basic',
            'column_mapping' => $data['column_mapping'] ?? [],
            'queue_name' => 'bulk-import'
        ]);
    }

    /**
     * Update log with progress
     */
    public function updateProgress(array $progress): void
    {
        $this->update([
            'processed_rows' => $progress['processed'] ?? $this->processed_rows,
            'success_count' => $progress['success_count'] ?? $this->success_count,
            'error_count' => $progress['error_count'] ?? $this->error_count,
            'status' => $progress['status'] ?? $this->status
        ]);
    }

    /**
     * Complete the import log
     */
    public function complete(array $results): void
    {
        $completedAt = now();
        $processingTime = $this->started_at ? $completedAt->diffInSeconds($this->started_at) : 0;

        $this->update([
            'processed_rows' => $results['total_processed'] ?? $this->processed_rows,
            'success_count' => $results['success_count'] ?? $this->success_count,
            'error_count' => $results['error_count'] ?? $this->error_count,
            'status' => $results['status'] ?? 'completed',
            'completed_at' => $completedAt,
            'processing_time_seconds' => $processingTime,
            'error_details' => $results['errors'] ?? [],
            'created_stores_data' => $results['created_stores'] ?? [],
            'memory_usage_mb' => memory_get_peak_usage(true) / 1024 / 1024
        ]);
    }

    /**
     * Get statistics for dashboard
     */
    public static function getStatistics(int $days = 30): array
    {
        $query = self::recent($days);

        return [
            'total_imports' => $query->count(),
            'successful_imports' => $query->successful()->count(),
            'failed_imports' => $query->failed()->count(),
            'processing_imports' => $query->processing()->count(),
            'total_stores_created' => $query->sum('success_count'),
            'total_errors' => $query->sum('error_count'),
            'average_success_rate' => $query->avg('success_count') ?: 0,
            'average_processing_time' => $query->whereNotNull('processing_time_seconds')->avg('processing_time_seconds') ?: 0
        ];
    }

    /**
     * Get chart data for dashboard
     */
    public static function getChartData(int $days = 7): array
    {
        $dates = [];
        $imports = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates[] = $date->format('M d');
            $imports[] = self::whereDate('created_at', $date)->count();
        }

        $statusCounts = [
            self::recent($days)->successful()->count(),
            self::recent($days)->failed()->count(),
            self::recent($days)->processing()->count()
        ];

        return [
            'dates' => $dates,
            'imports' => $imports,
            'status' => $statusCounts
        ];
    }
}