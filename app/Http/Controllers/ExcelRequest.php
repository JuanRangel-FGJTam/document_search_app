<?php
namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class ExcelRequest
{
    private $data;

    public function create($data)
    {
        $this->data = $data;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 5;

        // Encabezado de la empresa y dirección
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->setCellValue('A1', 'Fiscalía General de Justicia del Estado de Tamaulipas');
        $sheet->setCellValue('A2', 'Reporte de Solicitudes - Estado: ' . ($data['status_name'] ?? 'Todos'));

        // Alineación del encabezado
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:J3')->getFont()->setBold(true);

        // Definir los meses en inglés y traducirlos al español
        $englishMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $spanishMonths = array_map(function ($month) {
            return Carbon::parse("2023-{$month}-01")->locale('es')->translatedFormat('F');
        }, $englishMonths);

        // Primera columna con los tipos de identificación
        $sheet->setCellValue('A' . $row, 'Tipo de Identificación');
        $col = 'B';
        foreach ($spanishMonths as $month) {
            $sheet->setCellValue($col . $row, $month);
            $col++;
        }

        // Aplicar estilos a la cabecera
        $sheet->getStyle('A' . $row . ':' . $col . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Obtener todos los tipos de identificación únicos
        $identifications = collect();
        foreach ($data['data'] as $monthData) {
            // Convertir el array 'identifications_count' en una colección
            $identifications = $identifications->merge(collect($monthData['identifications_count'])->keys());
        }
        $identifications = $identifications->unique();

        // Llenar la tabla con los datos agrupados
        $dataRows = [];
        foreach ($identifications as $identificationType) {
            $dataRow = ['identification' => $identificationType];
            foreach ($englishMonths as $month) {
                // Traducir el mes al español para acceder a los datos
                $spanishMonth = Carbon::parse("2023-{$month}-01")->locale('es')->translatedFormat('F');
                $dataRow[$spanishMonth] = $data['data'][$month]['identifications_count'][$identificationType] ?? 0;
            }
            $dataRows[] = $dataRow;
        }

        // Escribir los datos en el Excel
        foreach ($dataRows as $rowData) {
            $sheet->setCellValue('A' . $row, $rowData['identification']);
            $col = 'B';
            foreach ($spanishMonths as $month) {
                $sheet->setCellValue($col . $row, $rowData[$month]);
                $col++;
            }
            $row++;
        }

        // Agregar la fila de totales
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $col = 'B';
        foreach ($spanishMonths as $month) {
            $totalMonth = array_sum(array_column($dataRows, $month));
            $sheet->setCellValue($col . $row, $totalMonth);
            $col++;
        }

        // Aplicar estilos a la fila de totales
        $sheet->getStyle('A' . $row . ':' . $col . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF666666');
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);

        $writer = new Xlsx($spreadsheet);

        // Limpiar buffers de salida
        if (ob_get_contents()) ob_end_clean();

        // Retornar el archivo Excel
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Reporte_Solicitudes.xlsx"',
            ]
        );
    }
}
