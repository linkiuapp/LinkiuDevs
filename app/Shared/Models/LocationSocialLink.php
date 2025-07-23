<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSocialLink extends Model
{
    protected $fillable = [
        'location_id', 'platform', 'url'
    ];
    
    /**
     * Get the location that owns the social link.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    
    /**
     * Get available social media platforms.
     */
    public static function getPlatforms(): array
    {
        return [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'youtube' => 'YouTube',
            'whatsapp' => 'WhatsApp',
            'linkiu' => 'Linkiu'
        ];
    }
    
    /**
     * Get the icon for this platform.
     */
    public function getPlatformIcon(): string
    {
        $icons = [
            'instagram' => 'solar-link-linear',
            'facebook' => 'solar-facebook-circle-linear',
            'tiktok' => 'solar-music-note-2-linear',
            'youtube' => 'solar-play-circle-linear',
            'whatsapp' => 'solar-chat-round-line-linear',
            'linkiu' => 'solar-link-circle-linear'
        ];
        
        return $icons[$this->platform] ?? 'solar-link-circle-linear';
    }
    
    /**
     * Get the color for this platform.
     */
    public function getPlatformColor(): string
    {
        $colors = [
            'instagram' => 'text-pink-600',
            'facebook' => 'text-blue-600',
            'tiktok' => 'text-black-400',
            'youtube' => 'text-red-600',
            'whatsapp' => 'text-green-600',
            'linkiu' => 'text-primary-200'
        ];
        
        return $colors[$this->platform] ?? 'text-primary-200';
    }
    
    /**
     * Validate URL format for the given platform.
     */
    public static function validateUrl(string $platform, string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $patterns = [
            'instagram' => '/instagram\.com/i',
            'facebook' => '/facebook\.com|fb\.com/i',
            'tiktok' => '/tiktok\.com/i',
            'youtube' => '/youtube\.com|youtu\.be/i',
            'whatsapp' => '/wa\.me|whatsapp\.com/i',
            'linkiu' => '/linkiu\.bio/i'
        ];
        
        if (isset($patterns[$platform])) {
            return preg_match($patterns[$platform], $url) === 1;
        }
        
        return true;
    }
    
    /**
     * Format WhatsApp URL with message.
     */
    public static function formatWhatsAppUrl(string $phone, ?string $message = null): string
    {
        // Clean phone number
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ensure it starts with +
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        
        $url = "https://wa.me/" . substr($phone, 1); // Remove + for wa.me format
        
        if ($message) {
            $url .= '?text=' . urlencode($message);
        }
        
        return $url;
    }
}