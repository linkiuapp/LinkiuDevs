<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Slider extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'image_path',
        'url',
        'url_type',
        'is_active',
        'is_scheduled',
        'is_permanent',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'scheduled_days',
        'sort_order',
        'transition_duration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_scheduled' => 'boolean',
        'is_permanent' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'scheduled_days' => 'array',
        'sort_order' => 'integer',
    ];

    // Constantes para tipos de URL
    const URL_TYPE_INTERNAL = 'internal';
    const URL_TYPE_EXTERNAL = 'external';
    const URL_TYPE_NONE = 'none';

    const URL_TYPES = [
        self::URL_TYPE_INTERNAL => 'Interna',
        self::URL_TYPE_EXTERNAL => 'Externa',
        self::URL_TYPE_NONE => 'Sin enlace',
    ];

    // Constantes para duración de transición
    const TRANSITION_3_SEC = '3';
    const TRANSITION_5_SEC = '5';
    const TRANSITION_7_SEC = '7';

    const TRANSITION_DURATIONS = [
        self::TRANSITION_3_SEC => '3 segundos',
        self::TRANSITION_5_SEC => '5 segundos',
        self::TRANSITION_7_SEC => '7 segundos',
    ];

    // Días de la semana
    const DAYS_OF_WEEK = [
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar sort_order al crear
        static::creating(function ($slider) {
            if (is_null($slider->sort_order)) {
                $slider->sort_order = static::where('store_id', $slider->store_id)->max('sort_order') + 1;
            }
        });

        // Eliminar imagen al eliminar slider
        static::deleting(function ($slider) {
            if ($slider->image_path && Storage::disk('s3')->exists($slider->image_path)) {
                Storage::disk('s3')->delete($slider->image_path);
            }
        });
    }

    /**
     * Relación con Store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Shared\Models\Store::class);
    }

    /**
     * Scope para sliders activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para sliders por tienda
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope para sliders ordenados
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Scope para sliders que deben mostrarse ahora
     */
    public function scopeCurrentlyVisible($query)
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l')); // monday, tuesday, etc.
        $currentTime = $now->format('H:i');

        return $query->where('is_active', true)
            ->where(function ($q) use ($now, $today, $currentTime) {
                $q->where('is_scheduled', false)
                  ->orWhere(function ($subQuery) use ($now, $today, $currentTime) {
                      $subQuery->where('is_scheduled', true)
                               ->where(function ($dateQuery) use ($now) {
                                   $dateQuery->where('is_permanent', true)
                                            ->orWhere(function ($rangeQuery) use ($now) {
                                                $rangeQuery->where('start_date', '<=', $now->toDateString())
                                                          ->where('end_date', '>=', $now->toDateString());
                                            });
                               })
                               ->where(function ($dayQuery) use ($today) {
                                   $dayQuery->whereNull('scheduled_days')
                                           ->orWhereJsonContains('scheduled_days->' . $today, true);
                               })
                               ->where(function ($timeQuery) use ($currentTime) {
                                   $timeQuery->whereNull('start_time')
                                           ->orWhere(function ($rangeTimeQuery) use ($currentTime) {
                                               $rangeTimeQuery->where('start_time', '<=', $currentTime)
                                                             ->where('end_time', '>=', $currentTime);
                                           });
                               });
                  });
            });
    }

    /**
     * Accessor para la URL de la imagen
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return asset('assets/images/slider-placeholder.jpg');
    }

    /**
     * Accessor para el tipo de URL formateado
     */
    public function getUrlTypeFormattedAttribute(): string
    {
        return self::URL_TYPES[$this->url_type] ?? 'Sin enlace';
    }

    /**
     * Accessor para la duración de transición formateada
     */
    public function getTransitionDurationFormattedAttribute(): string
    {
        return self::TRANSITION_DURATIONS[$this->transition_duration] ?? '5 segundos';
    }

    /**
     * Accessor para los días programados formateados
     */
    public function getScheduledDaysFormattedAttribute(): string
    {
        if (!$this->scheduled_days) {
            return 'Todos los días';
        }

        $activeDays = collect($this->scheduled_days)
            ->filter()
            ->keys()
            ->map(fn($day) => self::DAYS_OF_WEEK[$day] ?? $day)
            ->join(', ');

        return $activeDays ?: 'Ningún día';
    }

    /**
     * Verificar si el slider está actualmente visible
     */
    public function isCurrentlyVisible(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->is_scheduled) {
            return true;
        }

        $now = Carbon::now();
        $today = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        // Verificar fechas
        if (!$this->is_permanent) {
            if ($this->start_date && $now->toDateString() < $this->start_date) {
                return false;
            }
            if ($this->end_date && $now->toDateString() > $this->end_date) {
                return false;
            }
        }

        // Verificar días de la semana
        if ($this->scheduled_days && !($this->scheduled_days[$today] ?? false)) {
            return false;
        }

        // Verificar horarios
        if ($this->start_time && $this->end_time) {
            if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validar URL interna
     */
    public function validateInternalUrl(): bool
    {
        if ($this->url_type !== self::URL_TYPE_INTERNAL || !$this->url) {
            return true;
        }

        $url = ltrim($this->url, '/');
        $segments = explode('/', $url);

        // Validar rutas de productos: /producto/slug
        if (isset($segments[0]) && $segments[0] === 'producto' && isset($segments[1])) {
            return Product::where('slug', $segments[1])
                         ->where('store_id', $this->store_id)
                         ->exists();
        }

        // Validar rutas de categorías: /categoria/slug
        if (isset($segments[0]) && $segments[0] === 'categoria' && isset($segments[1])) {
            return Category::where('slug', $segments[1])
                          ->where('store_id', $this->store_id)
                          ->exists();
        }

        return false;
    }

    /**
     * Generar nombre único para duplicar
     */
    public static function generateUniqueName(string $baseName, int $storeId): string
    {
        $counter = 1;
        $name = $baseName;

        while (static::where('name', $name)->where('store_id', $storeId)->exists()) {
            $name = $baseName . ' (' . $counter . ')';
            $counter++;
        }

        return $name;
    }
} 