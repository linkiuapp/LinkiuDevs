<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EmailSecurityService
{
    /**
     * Validate email address with enhanced security checks
     */
    public static function validateEmailAddress(string $email): array
    {
        $issues = [];
        
        // Basic format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $issues[] = 'Formato de email inválido';
            return ['valid' => false, 'issues' => $issues];
        }
        
        // Length validation (RFC 5321)
        if (strlen($email) > 254) {
            $issues[] = 'Email demasiado largo (máximo 254 caracteres)';
        }
        
        // Domain validation
        $domain = substr(strrchr($email, "@"), 1);
        if (!$domain) {
            $issues[] = 'Dominio de email inválido';
        } else {
            // Check for suspicious domains
            $suspiciousDomains = [
                'tempmail.org', '10minutemail.com', 'guerrillamail.com', 
                'mailinator.com', 'throwaway.email', 'temp-mail.org'
            ];
            
            if (in_array($domain, $suspiciousDomains)) {
                $issues[] = 'Dominio de email no permitido';
            }
            
            // Check for valid TLD
            if (!preg_match('/\.[a-zA-Z]{2,}$/', $domain)) {
                $issues[] = 'Dominio de email inválido';
            }
        }
        
        // Local part validation
        $localPart = substr($email, 0, strrpos($email, '@'));
        if (strlen($localPart) > 64) {
            $issues[] = 'Parte local del email demasiado larga (máximo 64 caracteres)';
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }
    
    /**
     * Sanitize HTML content for email templates
     */
    public static function sanitizeHtmlContent(string $html): string
    {
        // Define allowed tags and attributes
        $allowedTags = '<p><br><strong><b><em><i><ul><li><ol><a><div><span><h1><h2><h3><h4><h5><h6>';
        
        // First pass: strip dangerous tags
        $html = strip_tags($html, $allowedTags);
        
        // Remove dangerous attributes and JavaScript
        $html = preg_replace('/(<[^>]*)\s(on\w+|javascript:|vbscript:|data:)[^>]*>/i', '$1>', $html);
        
        // Clean up href attributes in links (only allow http, https, mailto)
        $html = preg_replace_callback('/<a\s+([^>]*href\s*=\s*["\']?)([^"\'>\s]+)(["\']?[^>]*)>/i', function($matches) {
            $url = $matches[2];
            if (!preg_match('/^(https?:\/\/|mailto:)/i', $url)) {
                return '<a ' . $matches[1] . '#' . $matches[3] . '>';
            }
            return $matches[0];
        }, $html);
        
        // Remove any remaining script content
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        
        // Remove style attributes that could contain JavaScript
        $html = preg_replace('/style\s*=\s*["\'][^"\']*["\']/', '', $html);
        
        return $html;
    }
    
    /**
     * Validate template variables
     */
    public static function validateTemplateVariables(string $content, array $allowedVariables): array
    {
        $issues = [];
        
        // Find all variables in content
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $variable) {
                $fullVar = '{{' . trim($variable) . '}}';
                if (!in_array($fullVar, $allowedVariables)) {
                    $issues[] = "Variable '{$fullVar}' no está disponible";
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Check rate limiting for email configuration changes
     */
    public static function checkConfigurationRateLimit(int $userId, string $ipAddress): array
    {
        $key = "email-config:{$userId}:{$ipAddress}";
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            
            return [
                'allowed' => false,
                'retry_after' => $seconds,
                'message' => 'Demasiados intentos de configuración. Intenta nuevamente en ' . ceil($seconds / 60) . ' minutos.'
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        Log::warning("Email Security Event: {$event}", array_merge([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ], $context));
    }
    
    /**
     * Validate template content for security issues
     */
    public static function validateTemplateContent(string $content): array
    {
        $issues = [];
        
        // Check for potentially dangerous HTML tags
        $dangerousTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'meta', 'link'];
        foreach ($dangerousTags as $tag) {
            if (stripos($content, "<{$tag}") !== false) {
                $issues[] = "Etiqueta HTML peligrosa detectada: {$tag}";
            }
        }
        
        // Check for JavaScript in attributes
        if (preg_match('/\s(on\w+|javascript:|vbscript:)/i', $content)) {
            $issues[] = 'JavaScript detectado en atributos HTML';
        }
        
        // Check for data URLs that could contain scripts
        if (preg_match('/data:\s*[^;]*;base64/i', $content)) {
            $issues[] = 'URLs de datos base64 no permitidas';
        }
        
        // Check for external resource loading
        if (preg_match('/(src|href)\s*=\s*["\']?https?:\/\//i', $content)) {
            $issues[] = 'Referencias a recursos externos detectadas';
        }
        
        return $issues;
    }
    
    /**
     * Generate secure audit log entry
     */
    public static function auditConfigurationChange(string $action, array $changes, array $metadata = []): void
    {
        Log::info("Email Configuration Audit: {$action}", [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'unknown',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'changes' => $changes,
            'metadata' => $metadata,
            'timestamp' => now(),
            'session_id' => session()->getId()
        ]);
    }
}