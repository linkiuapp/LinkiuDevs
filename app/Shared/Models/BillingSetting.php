<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo_url',
        'company_name',
        'company_address',
        'tax_id',
        'phone',
        'email',
        'footer_text',
    ];

    /**
     * Get the singleton billing settings instance
     */
    public static function getInstance(): self
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'company_name' => 'Linkiu',
                'company_address' => 'Carrera 7 #32-16, Bogotá, Colombia',
                'tax_id' => '900.123.456-7',
                'phone' => '+57 (1) 234-5678',
                'email' => 'facturacion@linkiu.bio',
                'footer_text' => 'Gracias por confiar en Linkiu para impulsar tu negocio digital.',
            ]);
        }
        
        return $settings;
    }

    /**
     * Get formatted company info for invoices
     */
    public function getFormattedCompanyInfo(): array
    {
        return [
            'name' => $this->company_name,
            'address' => $this->company_address,
            'tax_id' => $this->tax_id,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo_url,
        ];
    }

    /**
     * Check if company info is complete
     */
    public function isComplete(): bool
    {
        return !empty($this->company_name) &&
               !empty($this->company_address) &&
               !empty($this->tax_id) &&
               !empty($this->email);
    }

    /**
     * Get missing required fields
     */
    public function getMissingFields(): array
    {
        $required = ['company_name', 'company_address', 'tax_id', 'email'];
        $missing = [];

        foreach ($required as $field) {
            if (empty($this->{$field})) {
                $missing[] = $field;
            }
        }

        return $missing;
    }
}
