<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'response_type',
        'is_public',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_public' => 'boolean',
    ];

    // Relaciones
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('response_type', $type);
    }

    // Getters
    public function getResponseTypeLabelAttribute(): string
    {
        return match($this->response_type) {
            'response' => 'Respuesta',
            'internal_note' => 'Nota Interna',
            'status_change' => 'Cambio de Estado',
            default => 'Respuesta'
        };
    }

    public function getIsFromAdminAttribute(): bool
    {
        // Un admin es:
        // 1. Un super_admin (equipo de soporte de Linkiu)
        // 2. Un store_admin cuando está respondiendo desde el contexto de SuperLinkiu
        return $this->user && $this->user->isSuperAdmin();
    }

    public function getIsSupportTeamAttribute(): bool
    {
        // Solo el equipo de soporte de Linkiu (super_admin)
        return $this->user && $this->user->isSuperAdmin();
    }

    // Helper para generar URLs de attachments de respuestas
    public function getAttachmentUrl($attachment): string
    {
        // Usar la URL directa del bucket S3
        return Storage::disk('s3')->url($attachment['path']);
    }

    public function getFormattedMessageAttribute(): string
    {
        return nl2br(e($this->message));
    }
} 