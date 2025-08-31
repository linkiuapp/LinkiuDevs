<?php

namespace App\Features\SuperLinkiu\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BulkImportResultsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function array(): array
    {
        $data = [];

        // Add summary information
        $data[] = ['RESUMEN DE IMPORTACIÓN'];
        $data[] = ['Total Procesadas', $this->results['total_processed']];
        $data[] = ['Tiendas Creadas', $this->results['success_count']];
        $data[] = ['Errores', $this->results['error_count']];
        $data[] = ['Fecha de Procesamiento', $this->results['completed_at']];
        $data[] = []; // Empty row

        // Add created stores section
        if (!empty($this->results['created_stores'])) {
            $data[] = ['TIENDAS CREADAS EXITOSAMENTE'];
            $data[] = ['ID', 'Nombre', 'URL', 'Email Admin', 'Plan'];
            
            foreach ($this->results['created_stores'] as $store) {
                $data[] = [
                    $store['id'],
                    $store['name'],
                    $store['slug'],
                    $store['admin_email'],
                    $store['plan_name']
                ];
            }
            $data[] = []; // Empty row
        }

        // Add errors section
        if (!empty($this->results['errors'])) {
            $data[] = ['ERRORES ENCONTRADOS'];
            $data[] = ['Fila', 'Error', 'Datos'];
            
            foreach ($this->results['errors'] as $error) {
                $data[] = [
                    $error['row'],
                    $error['message'],
                    is_array($error['data']) ? json_encode($error['data']) : $error['data']
                ];
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return []; // We handle headings in the array() method
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $currentRow = 1;

        // Style summary section
        $styles["A{$currentRow}"] = [
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E3F2FD']]
        ];
        $currentRow += 6; // Skip summary rows

        // Style created stores section
        if (!empty($this->results['created_stores'])) {
            $styles["A{$currentRow}"] = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F5E8']]
            ];
            $currentRow++;
            
            // Header row for stores
            $styles["A{$currentRow}:E{$currentRow}"] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F0F0']]
            ];
            $currentRow += count($this->results['created_stores']) + 2;
        }

        // Style errors section
        if (!empty($this->results['errors'])) {
            $styles["A{$currentRow}"] = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFEBEE']]
            ];
            $currentRow++;
            
            // Header row for errors
            $styles["A{$currentRow}:C{$currentRow}"] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F0F0']]
            ];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 25,
            'D' => 30,
            'E' => 20,
        ];
    }

    public function title(): string
    {
        return 'Resultados Importación';
    }
}