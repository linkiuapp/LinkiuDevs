<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LocationSchedule extends Model
{
    protected $fillable = [
        'location_id', 'day_of_week', 'is_closed',
        'open_time_1', 'close_time_1', 'open_time_2', 'close_time_2'
    ];
    
    protected $casts = [
        'is_closed' => 'boolean',
        'day_of_week' => 'integer'
    ];
    
    /**
     * Get the location that owns the schedule.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    
    /**
     * Check if the schedule has an additional time slot.
     */
    public function hasAdditionalSchedule(): bool
    {
        return !empty($this->open_time_2) && !empty($this->close_time_2);
    }
    
    /**
     * Check if the schedule is open at the given time.
     */
    public function isOpenAt(Carbon $time): bool
    {
        if ($this->is_closed) {
            return false;
        }
        
        $timeString = $time->format('H:i:s');
        
        // Check primary schedule
        if ($this->isTimeInRange($timeString, $this->open_time_1, $this->close_time_1)) {
            return true;
        }
        
        // Check additional schedule if exists
        if ($this->hasAdditionalSchedule()) {
            if ($this->isTimeInRange($timeString, $this->open_time_2, $this->close_time_2)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the day name for this schedule.
     */
    public function getDayName(): string
    {
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
        
        return $days[$this->day_of_week] ?? 'Desconocido';
    }
    
    /**
     * Get formatted schedule for display.
     */
    public function getFormattedSchedule(): string
    {
        if ($this->is_closed) {
            return 'Cerrado';
        }
        
        $primary = $this->formatTimeRange($this->open_time_1, $this->close_time_1);
        
        if ($this->hasAdditionalSchedule()) {
            $additional = $this->formatTimeRange($this->open_time_2, $this->close_time_2);
            return "{$primary} y {$additional}";
        }
        
        return $primary;
    }
    
    /**
     * Format a time range for display.
     */
    protected function formatTimeRange(?string $start, ?string $end): string
    {
        if (empty($start) || empty($end)) {
            return '';
        }
        
        $startFormatted = Carbon::parse($start)->format('H:i');
        $endFormatted = Carbon::parse($end)->format('H:i');
        
        return "{$startFormatted} - {$endFormatted}";
    }
    
    /**
     * Helper method to check if a time is within a range.
     */
    protected function isTimeInRange(string $time, string $start, string $end): bool
    {
        // Handle overnight ranges (e.g., 22:00 - 02:00)
        if ($start > $end) {
            return $time >= $start || $time <= $end;
        }
        
        return $time >= $start && $time <= $end;
    }
}