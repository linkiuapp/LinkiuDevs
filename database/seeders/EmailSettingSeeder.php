<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailSetting;

class EmailSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'context' => 'store_management',
                'email' => 'no-responder@linkiu.email',
                'name' => 'Gestión de Tiendas',
                'is_active' => true
            ],
            [
                'context' => 'support',
                'email' => 'soporte@linkiu.email',
                'name' => 'Soporte',
                'is_active' => true
            ],
            [
                'context' => 'billing',
                'email' => 'contabilidad@linkiu.email',
                'name' => 'Facturación',
                'is_active' => true
            ]
        ];

        foreach ($settings as $setting) {
            EmailSetting::updateOrCreate(
                ['context' => $setting['context']],
                $setting
            );
        }
    }
}
