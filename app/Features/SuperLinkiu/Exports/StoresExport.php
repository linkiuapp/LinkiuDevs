<?php

namespace App\Features\SuperLinkiu\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class StoresExport implements FromCollection, WithHeadings, WithMapping
{
    protected $stores;

    public function __construct(Collection $stores)
    {
        $this->stores = $stores;
    }

    public function collection()
    {
        return $this->stores;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Email',
            'Plan',
            'Estado',
            'Verificada',
            'Tipo Documento',
            'Número Documento',
            'Teléfono',
            'País',
            'Departamento',
            'Ciudad',
            'Dirección',
            'Fecha Creación',
            'Última Actividad'
        ];
    }

    public function map($store): array
    {
        return [
            $store->id,
            $store->name,
            $store->email,
            $store->plan->name ?? 'Sin plan',
            $this->getStatusLabel($store->status),
            $store->verified ? 'Sí' : 'No',
            $store->document_type,
            $store->document_number,
            $store->phone,
            $store->country,
            $store->department,
            $store->city,
            $store->address,
            $store->created_at->format('d/m/Y H:i'),
            $store->last_active_at ? $store->last_active_at->format('d/m/Y H:i') : 'Nunca'
        ];
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'active' => 'Activa',
            'inactive' => 'Inactiva',
            'suspended' => 'Suspendida'
        ];

        return $labels[$status] ?? $status;
    }
} 