<?php

namespace App\Shared\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'role',
        'store_id',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Obtener la URL completa del avatar del usuario
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            // Usar storage local público
            return asset('storage/' . $this->avatar_path);
        }

        // Avatar por defecto usando UI Avatars
        $initials = $this->getInitialsAttribute();
        return "https://ui-avatars.com/api/?name={$initials}&color=7432F8&background=F1EAFF&size=128";
    }

    /**
     * Get user initials for avatar fallback
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is store admin
     */
    public function isStoreAdmin(): bool
    {
        return $this->role === 'store_admin';
    }

    /**
     * Check if the user is an admin (either super_admin or store_admin)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin';
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get the store that owns the user
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the store drafts for this user
     */
    public function storeDrafts()
    {
        return $this->hasMany(\App\Models\StoreDraft::class);
    }

    /**
     * Get the active store drafts for this user
     */
    public function activeStoreDrafts()
    {
        return $this->storeDrafts()->active();
    }

    /**
     * Get the latest active store draft for this user
     */
    public function latestStoreDraft()
    {
        return $this->activeStoreDrafts()->latest()->first();
    }
}
