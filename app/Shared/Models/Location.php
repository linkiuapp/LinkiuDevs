<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\BelongsToTenant;
use Carbon\Carbon;

class Location extends Model
{
    use BelongsToTenant;
    
    protected $table = 'store_locations';
    
    protected $fillable = [
        'store_id', 'name', 'description', 'manager_name',
        'phone', 'whatsapp', 'department', 'city', 'address',
        'is_main', 'is_active', 'whatsapp_message'
    ];
    
    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
        'whatsapp_clicks' => 'integer'
    ];
    
    /**
     * Get the store that owns the location.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    
    /**
     * Get the schedules for the location.
     */
    public function schedules()
    {
        return $this->hasMany(LocationSchedule::class, 'location_id');
    }
    
    /**
     * Get the social links for the location.
     */
    public function socialLinks()
    {
        return $this->hasMany(LocationSocialLink::class, 'location_id');
    }
    
    /**
     * Check if the location is currently open based on schedules.
     */
    public function isCurrentlyOpen(): bool
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i:s');
        
        $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();
        
        if (!$schedule || $schedule->is_closed) {
            return false;
        }
        
        // Check primary schedule
        if ($this->isTimeInRange($currentTime, $schedule->open_time_1, $schedule->close_time_1)) {
            return true;
        }
        
        // Check additional schedule if exists
        if ($schedule->open_time_2 && $schedule->close_time_2) {
            if ($this->isTimeInRange($currentTime, $schedule->open_time_2, $schedule->close_time_2)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the current status of the location.
     */
    public function getCurrentStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i:s');
        
        $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();
        
        if (!$schedule || $schedule->is_closed) {
            return 'closed';
        }
        
        // Check primary schedule
        if ($this->isTimeInRange($currentTime, $schedule->open_time_1, $schedule->close_time_1)) {
            return 'open';
        }
        
        // Check additional schedule if exists
        if ($schedule->open_time_2 && $schedule->close_time_2) {
            if ($this->isTimeInRange($currentTime, $schedule->open_time_2, $schedule->close_time_2)) {
                return 'open';
            }
            
            // Check if between schedules (temporarily closed)
            if ($currentTime > $schedule->close_time_1 && $currentTime < $schedule->open_time_2) {
                return 'temporarily_closed';
            }
        }
        
        return 'closed';
    }
    
    /**
     * Get the next status change time.
     */
    public function getNextStatusChange(): ?Carbon
    {
        if (!$this->is_active) {
            return null;
        }
        
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i:s');
        
        $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();
        
        if (!$schedule || $schedule->is_closed) {
            // Find next day that's open
            for ($i = 1; $i <= 7; $i++) {
                $nextDay = ($dayOfWeek + $i) % 7;
                $nextSchedule = $this->schedules()->where('day_of_week', $nextDay)->first();
                
                if ($nextSchedule && !$nextSchedule->is_closed) {
                    $daysToAdd = $i;
                    $nextOpenTime = Carbon::parse($nextSchedule->open_time_1)->addDays($daysToAdd);
                    return $nextOpenTime;
                }
            }
            
            return null;
        }
        
        // Currently open in primary schedule
        if ($this->isTimeInRange($currentTime, $schedule->open_time_1, $schedule->close_time_1)) {
            return Carbon::parse($schedule->close_time_1);
        }
        
        // Currently open in additional schedule
        if ($schedule->open_time_2 && $schedule->close_time_2 && 
            $this->isTimeInRange($currentTime, $schedule->open_time_2, $schedule->close_time_2)) {
            return Carbon::parse($schedule->close_time_2);
        }
        
        // Before primary schedule
        if ($currentTime < $schedule->open_time_1) {
            return Carbon::parse($schedule->open_time_1);
        }
        
        // Between schedules
        if ($schedule->open_time_2 && $currentTime > $schedule->close_time_1 && $currentTime < $schedule->open_time_2) {
            return Carbon::parse($schedule->open_time_2);
        }
        
        // After all schedules, find next day
        for ($i = 1; $i <= 7; $i++) {
            $nextDay = ($dayOfWeek + $i) % 7;
            $nextSchedule = $this->schedules()->where('day_of_week', $nextDay)->first();
            
            if ($nextSchedule && !$nextSchedule->is_closed) {
                $daysToAdd = $i;
                $nextOpenTime = Carbon::parse($nextSchedule->open_time_1)->addDays($daysToAdd);
                return $nextOpenTime;
            }
        }
        
        return null;
    }
    
    /**
     * Set this location as the main location for the store.
     */
    public function setAsMain(): void
    {
        // Begin transaction
        \DB::beginTransaction();
        
        try {
            // Remove main status from all other locations
            Location::where('store_id', $this->store_id)
                ->where('id', '!=', $this->id)
                ->update(['is_main' => false]);
            
            // Set this location as main
            $this->is_main = true;
            $this->save();
            
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Increment the WhatsApp clicks counter.
     */
    public function incrementWhatsAppClicks(): void
    {
        $this->increment('whatsapp_clicks');
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