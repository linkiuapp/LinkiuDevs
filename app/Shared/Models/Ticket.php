<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'store_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at',
        'metadata',
        'attachments',
        'last_viewed_by_store_admin_at',
        'last_viewed_by_super_admin_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'attachments' => 'array',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_viewed_by_store_admin_at' => 'datetime',
        'last_viewed_by_super_admin_at' => 'datetime',
    ];

    // Helper para generar URLs de attachments
    public function getAttachmentUrl($attachment): string
    {
        // Usar la URL directa del bucket S3
        return Storage::disk('s3')->url($attachment['path']);
    }

    // Verificar si hay respuestas nuevas del soporte (para TenantAdmin)
    public function getHasNewSupportResponsesAttribute(): bool
    {
        $lastViewed = $this->last_viewed_by_store_admin_at ?? $this->created_at;
        
        return $this->responses()
            ->whereHas('user', function($query) {
                $query->where('role', 'super_admin');
            })
            ->where('created_at', '>', $lastViewed)
            ->exists();
    }

    // Verificar si hay respuestas nuevas de admin de tienda (para SuperLinkiu)
    public function getHasNewStoreResponsesAttribute(): bool
    {
        $lastViewed = $this->last_viewed_by_super_admin_at ?? $this->created_at;
        
        return $this->responses()
            ->whereHas('user', function($query) {
                $query->where('role', 'store_admin');
            })
            ->where('created_at', '>', $lastViewed)
            ->exists();
    }

    // Contar respuestas nuevas del soporte
    public function getNewSupportResponsesCountAttribute(): int
    {
        $lastViewed = $this->last_viewed_by_store_admin_at ?? $this->created_at;
        
        return $this->responses()
            ->whereHas('user', function($query) {
                $query->where('role', 'super_admin');
            })
            ->where('created_at', '>', $lastViewed)
            ->count();
    }

    // Contar respuestas nuevas de admin de tienda  
    public function getNewStoreResponsesCountAttribute(): int
    {
        $lastViewed = $this->last_viewed_by_super_admin_at ?? $this->created_at;
        
        return $this->responses()
            ->whereHas('user', function($query) {
                $query->where('role', 'store_admin');
            })
            ->where('created_at', '>', $lastViewed)
            ->count();
    }

    // Marcar como visto por el usuario actual
    public function markAsViewedBy(User $user): void
    {
        if ($user->isSuperAdmin()) {
            $this->update(['last_viewed_by_super_admin_at' => now()]);
        } elseif ($user->isStoreAdmin()) {
            $this->update(['last_viewed_by_store_admin_at' => now()]);
        }
    }

    // Marcar como visto por el usuario autenticado
    public function markAsViewed(): void
    {
        if ($user = auth()->user()) {
            $this->markAsViewedBy($user);
        }
    }

    // Relaciones
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category', 'slug');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class)->orderBy('created_at', 'asc');
    }

    public function publicResponses(): HasMany
    {
        return $this->hasMany(TicketResponse::class)->where('is_public', true)->orderBy('created_at', 'asc');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    // Métodos de negocio
    public function markAsInProgress($userId = null): bool
    {
        $this->status = 'in_progress';
        if ($userId && !$this->assigned_to) {
            $this->assigned_to = $userId;
        }
        return $this->save();
    }

    public function markAsResolved($userId = null): bool
    {
        $this->status = 'resolved';
        $this->resolved_at = now();
        if ($userId && !$this->assigned_to) {
            $this->assigned_to = $userId;
        }
        return $this->save();
    }

    public function markAsClosed($userId = null): bool
    {
        $this->status = 'closed';
        $this->closed_at = now();
        if (!$this->resolved_at) {
            $this->resolved_at = now();
        }
        if ($userId && !$this->assigned_to) {
            $this->assigned_to = $userId;
        }
        return $this->save();
    }

    public function assignTo($userId): bool
    {
        $this->assigned_to = $userId;
        if ($this->status === 'open') {
            $this->status = 'in_progress';
        }
        return $this->save();
    }

    public function addResponse($userId, $message, $responseType = 'response', $isPublic = true): TicketResponse
    {
        return $this->responses()->create([
            'user_id' => $userId,
            'message' => $message,
            'response_type' => $responseType,
            'is_public' => $isPublic,
        ]);
    }

    // Getters
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => 'Media'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Abierto',
            'in_progress' => 'En Progreso',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado',
            default => 'Abierto'
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        // Usar la nueva tabla de categorías si existe la relación
        if ($this->categoryModel) {
            return $this->categoryModel->name;
        }

        // Fallback para compatibilidad con tickets existentes
        return match($this->category) {
            'technical' => 'Técnico',
            'billing' => 'Facturación',
            'general' => 'General',
            'feature_request' => 'Solicitud de Función',
            default => 'General'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'error',
            default => 'info'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'info',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            default => 'info'
        };
    }

    public function getResponseTimeAttribute(): ?int
    {
        $firstResponse = $this->responses()->first();
        if (!$firstResponse) {
            return null;
        }
        return $this->created_at->diffInHours($firstResponse->created_at);
    }

    public function getResolutionTimeAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }
        return $this->created_at->diffInHours($this->resolved_at);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        $hours = match($this->priority) {
            'urgent' => 4,
            'high' => 24,
            'medium' => 72,
            'low' => 168,
            default => 72
        };

        return $this->created_at->addHours($hours)->isPast();
    }

    // Generación automática de número de ticket
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $storeSlug = null;
                if ($ticket->store_id) {
                    $store = Store::find($ticket->store_id);
                    $storeSlug = $store ? $store->slug : null;
                }
                $ticket->ticket_number = static::generateTicketNumber($storeSlug);
            }
        });
    }

    public static function generateTicketNumber($storeSlug = null): string
    {
        $prefix = 'TK';
        
        if ($storeSlug) {
            // Formato: TK-{slug_tienda}-001
            $storeSlugUpper = strtoupper($storeSlug);
            
            // Buscar el último ticket de la tienda
            $lastTicket = static::where('ticket_number', 'like', "{$prefix}-{$storeSlugUpper}-%")
                ->orderBy('ticket_number', 'desc')
                ->first();

            if ($lastTicket) {
                // Extraer el número secuencial
                preg_match('/-(\d+)$/', $lastTicket->ticket_number, $matches);
                $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return sprintf('%s-%s-%03d', $prefix, $storeSlugUpper, $newNumber);
        }

        // Formato legacy para SuperLinkiu: TK-202501-0001
        $year = date('Y');
        $month = date('m');
        $yearMonth = $year . $month;

        $lastTicket = static::where('ticket_number', 'like', "{$prefix}-{$yearMonth}%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%04d', $prefix, $yearMonth, $newNumber);
    }
} 