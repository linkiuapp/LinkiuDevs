<?php

namespace App\Features\SuperLinkiu\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Bulk Import Credentials Export
 * Generates comprehensive credential reports for bulk imports
 * Requirements: 7.6
 */
class BulkImportCredentialsExport implements WithMultipleSheets
{
    protected array $results;
    protected string $batchId;

    public function __construct(array $results, string $batchId)
    {
        $this->results = $results;
        $this->batchId = $batchId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Summary sheet
        $sheets[] = new BulkImportSummarySheet($this->results, $this->batchId);

        // Credentials sheet (only if there are successful stores)
        if (!empty($this->results['created_stores'])) {
            $sheets[] = new BulkImportCredentialsSheet($this->results['created_stores']);
        }

        // Errors sheet (only if there are errors)
        if (!empty($this->results['errors'])) {
            $sheets[] = new BulkImportErrorsSheet($this->results['errors']);
        }

        return $sheets;
    }
}

/**
 * Summary Sheet
 */
class BulkImportSummarySheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $results;
    protected string $batchId;

    public function __construct(array $results, string $batchId)
    {
        $this->results = $results;
        $this->batchId = $batchId;
    }

    public function array(): array
    {
        return [
            ['RESUMEN DE IMPORTACIÓN MASIVA'],
            [''],
            ['ID del Lote', $this->batchId],
            ['Fecha de Procesamiento', $this->results['completed_at'] ?? now()],
            ['Total de Registros', $this->results['total_processed'] ?? 0],
            ['Tiendas Creadas Exitosamente', $this->results['success_count'] ?? 0],
            ['Errores Encontrados', $this->results['error_count'] ?? 0],
            ['Tasa de Éxito', $this->calculateSuccessRate() . '%'],
            [''],
            ['ESTADÍSTICAS DETALLADAS'],
            [''],
            ['Tiempo de Procesamiento', $this->getProcessingTime()],
            ['Promedio por Registro', $this->getAverageTimePerRecord()],
            ['Memoria Utilizada', $this->getMemoryUsage()],
            [''],
            ['DISTRIBUCIÓN DE ERRORES'],
            ...$this->getErrorDistribution()
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1' => [
                'font' => ['bold' => true, 'size' => 16],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E3F2FD']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            'A3:A8' => ['font' => ['bold' => true]],
            'A10' => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F8FF']]
            ],
            'A16' => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFF8F0']]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 40,
        ];
    }

    public function title(): string
    {
        return 'Resumen';
    }

    private function calculateSuccessRate(): string
    {
        $total = $this->results['total_processed'] ?? 0;
        $success = $this->results['success_count'] ?? 0;
        
        if ($total === 0) return '0';
        
        return number_format(($success / $total) * 100, 2);
    }

    private function getProcessingTime(): string
    {
        // This would be calculated from actual processing data
        return 'N/A';
    }

    private function getAverageTimePerRecord(): string
    {
        // This would be calculated from actual processing data
        return 'N/A';
    }

    private function getMemoryUsage(): string
    {
        // This would be from actual memory usage data
        return 'N/A';
    }

    private function getErrorDistribution(): array
    {
        $errors = $this->results['errors'] ?? [];
        $distribution = [];
        $errorTypes = [];

        foreach ($errors as $error) {
            $type = $this->categorizeError($error['message'] ?? '');
            $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
        }

        foreach ($errorTypes as $type => $count) {
            $distribution[] = [$type, $count];
        }

        return $distribution;
    }

    private function categorizeError(string $message): string
    {
        if (str_contains($message, 'email')) {
            return 'Errores de Email';
        } elseif (str_contains($message, 'slug')) {
            return 'Errores de URL';
        } elseif (str_contains($message, 'required')) {
            return 'Campos Requeridos';
        } elseif (str_contains($message, 'plan')) {
            return 'Errores de Plan';
        } else {
            return 'Otros Errores';
        }
    }
}

/**
 * Credentials Sheet
 */
class BulkImportCredentialsSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $stores;

    public function __construct(array $stores)
    {
        $this->stores = $stores;
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->stores as $store) {
            $data[] = [
                $store['name'] ?? 'N/A',
                $store['slug'] ?? 'N/A',
                $store['admin_email'] ?? 'N/A',
                $this->generatePassword(), // In real implementation, this would come from the creation process
                $store['plan_name'] ?? 'N/A',
                $this->getFrontendUrl($store['slug'] ?? ''),
                $this->getAdminUrl($store['slug'] ?? ''),
                $store['created_at'] ?? now()->format('Y-m-d H:i:s'),
                'Activa'
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Nombre de la Tienda',
            'URL de la Tienda',
            'Email del Administrador',
            'Contraseña Temporal',
            'Plan Asignado',
            'URL Frontend',
            'URL Panel Admin',
            'Fecha de Creación',
            'Estado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            '1' => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F5E8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Nombre
            'B' => 20, // URL
            'C' => 30, // Email
            'D' => 20, // Contraseña
            'E' => 15, // Plan
            'F' => 35, // Frontend URL
            'G' => 35, // Admin URL
            'H' => 20, // Fecha
            'I' => 12, // Estado
        ];
    }

    public function title(): string
    {
        return 'Credenciales';
    }

    private function generatePassword(): string
    {
        // In real implementation, this would be the actual generated password
        // For security, passwords should not be stored in plain text
        return '***ENVIADA POR EMAIL***';
    }

    private function getFrontendUrl(string $slug): string
    {
        return url('/' . $slug);
    }

    private function getAdminUrl(string $slug): string
    {
        return url('/' . $slug . '/admin');
    }
}

/**
 * Errors Sheet
 */
class BulkImportErrorsSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->errors as $error) {
            $data[] = [
                $error['row'] ?? 'N/A',
                $error['message'] ?? 'Error desconocido',
                $this->categorizeError($error['message'] ?? ''),
                $this->getSeverity($error['message'] ?? ''),
                $this->getSuggestion($error['message'] ?? ''),
                json_encode($error['data'] ?? [], JSON_UNESCAPED_UNICODE)
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Fila',
            'Descripción del Error',
            'Categoría',
            'Severidad',
            'Sugerencia de Corrección',
            'Datos Originales'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            '1' => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFEBEE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // Fila
            'B' => 40, // Descripción
            'C' => 15, // Categoría
            'D' => 12, // Severidad
            'E' => 35, // Sugerencia
            'F' => 50, // Datos
        ];
    }

    public function title(): string
    {
        return 'Errores';
    }

    private function categorizeError(string $message): string
    {
        if (str_contains($message, 'email')) {
            return 'Email';
        } elseif (str_contains($message, 'slug')) {
            return 'URL';
        } elseif (str_contains($message, 'required')) {
            return 'Campo Requerido';
        } elseif (str_contains($message, 'plan')) {
            return 'Plan';
        } else {
            return 'Otro';
        }
    }

    private function getSeverity(string $message): string
    {
        if (str_contains($message, 'required')) {
            return 'Alta';
        } elseif (str_contains($message, 'unique') || str_contains($message, 'exists')) {
            return 'Media';
        } else {
            return 'Baja';
        }
    }

    private function getSuggestion(string $message): string
    {
        if (str_contains($message, 'email')) {
            return 'Verificar formato y unicidad del email';
        } elseif (str_contains($message, 'slug')) {
            return 'Usar una URL diferente o dejar vacío para generación automática';
        } elseif (str_contains($message, 'required')) {
            return 'Completar el campo requerido';
        } elseif (str_contains($message, 'plan')) {
            return 'Verificar que el ID del plan existe';
        } else {
            return 'Revisar los datos según el mensaje de error';
        }
    }
}