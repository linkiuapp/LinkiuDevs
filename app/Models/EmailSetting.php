<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class EmailSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'context',
        'email',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get email address for a specific context
     */
    public static function getEmail(string $context): string
    {
        $setting = static::where('context', $context)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            // Return default emails if no configuration exists
            return static::getDefaultEmail($context);
        }

        return $setting->email;
    }

    /**
     * Get all active email settings
     */
    public static function getActiveSettings(): Collection
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Update email for a specific context
     */
    public static function updateContext(string $context, string $email): bool
    {
        return static::updateOrCreate(
            ['context' => $context],
            [
                'email' => $email,
                'name' => static::getContextName($context),
                'is_active' => true
            ]
        ) ? true : false;
    }

    /**
     * Get default email for context
     */
    private static function getDefaultEmail(string $context): string
    {
        $defaults = [
            'store_management' => 'no-responder@linkiu.email',
            'support' => 'soporte@linkiu.email',
            'billing' => 'contabilidad@linkiu.email'
        ];

        return $defaults[$context] ?? 'no-responder@linkiu.email';
    }

    /**
     * Get context display name
     */
    private static function getContextName(string $context): string
    {
        $names = [
            'store_management' => 'Gestión de Tiendas',
            'support' => 'Soporte',
            'billing' => 'Facturación'
        ];

        return $names[$context] ?? ucfirst($context);
    }

    /**
     * Relationship with email templates
     */
    public function templates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class, 'context', 'context');
    }

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
