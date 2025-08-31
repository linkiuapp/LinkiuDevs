<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'store_id',
        'subscription_id',
        'plan_id',
        'amount',
        'period',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'metadata' => 'array',
    ];

    // Relaciones
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                     ->orWhere(function($q) {
                         $q->where('status', 'pending')
                           ->where('due_date', '<', now());
                     });
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    // Métodos de negocio
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->status === 'pending' && $this->due_date->isPast());
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function markAsPaid(Carbon $paidDate = null): bool
    {
        $this->status = 'paid';
        $this->paid_date = $paidDate ?? now();
        return $this->save();
    }

    public function markAsOverdue(): bool
    {
        if ($this->isPending() && $this->due_date->isPast()) {
            $this->status = 'overdue';
            return $this->save();
        }
        return false;
    }

    public function cancel(string $reason = null): bool
    {
        $this->status = 'cancelled';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['cancellation_reason'] = $reason;
            $metadata['cancelled_at'] = now()->toISOString();
            $this->metadata = $metadata;
        }
        return $this->save();
    }

    public function getFormattedAmount(): string
    {
        return '$' . number_format($this->amount, 0, ',', '.');
    }

    public function getDaysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    public function getPeriodLabel(): string
    {
        return match($this->period) {
            'monthly' => 'Mensual',
            'quarterly' => 'Trimestral',
            'biannual' => 'Semestral',
            default => 'Mensual'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagada',
            'overdue' => 'Vencida',
            'cancelled' => 'Cancelada',
            default => 'Pendiente'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'overdue' => 'error',
            'cancelled' => 'black',
            default => 'warning'
        };
    }

    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning-300 text-black-500',
            'paid' => 'bg-success-300 text-accent-50',
            'overdue' => 'bg-error-300 text-accent-50',
            'cancelled' => 'bg-black-300 text-accent-50',
            default => 'bg-warning-300 text-black-500'
        };
    }

    // Generar número de factura automático
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        
        // Buscar el último número de factura del mes actual
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}{$month}%")
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            // Extraer el número secuencial
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "INV-{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Boot method para generar número automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }
} 