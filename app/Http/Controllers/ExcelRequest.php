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
        $sheet->setCellValue(
            'A2',
            'Reporte de Solicitudes - Estado: ' . ($data['status_name'] ?? 'Todos') .
                (isset($data['municipality_name']) ? ' - Municipio ' . $data['municipality_name'] : '')
        );

        // Alineación del encabezado
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:J3')->getFont()->setBold(true);

        // Obtener el año actual y el mes actual
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $maxMonths = ($data['year'] == $currentYear) ? $currentMonth : 12;

        // Definir los meses hasta el mes actual si es el año en curso
        $englishMonths = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $spanishMonths = [
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ];

        $selectedMonths = array_slice($englishMonths, 0, $maxMonths);

        // Primera columna con los tipos de identificación
        $sheet->setCellValue('A' . $row, 'Tipo de Identificación');
        $col = 'B';
        foreach ($selectedMonths as $month) {
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
            $identifications = $identifications->merge(collect($monthData['identifications_count'])->keys());
        }
        $identifications = $identifications->unique();

        // Llenar la tabla con los datos agrupados
        $dataRows = [];
        foreach ($identifications as $identificationType) {
            $dataRow = ['identification' => $identificationType];
            foreach ($selectedMonths as $month) {
                $dataRow[$month] = $data['data'][$month]['identifications_count'][$identificationType] ?? 0;
            }
            $dataRows[] = $dataRow;
        }

        // Escribir los datos en el Excel
        foreach ($dataRows as $rowData) {
            $sheet->setCellValue('A' . $row, strtoupper($rowData['identification']));
            $col = 'B';
            foreach ($selectedMonths as $month) {
                $sheet->setCellValue($col . $row, $rowData[$month]);
                $col++;
            }
            $row++;
        }

        // Agregar la fila de totales
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $col = 'B';
        foreach ($selectedMonths as $month) {
            $totalMonth = array_sum(array_column($dataRows, $month));
            $sheet->setCellValue($col . $row, $totalMonth);
            $col++;
        }

        // Traducir los títulos de los meses al español
        $col = 'B';
        foreach ($selectedMonths as $index => $month) {
            $sheet->setCellValue($col . '5', $spanishMonths[$index]);
            $col++;
        }

        // Aplicar estilos a la fila de totales
        $sheet->getStyle('A' . $row . ':' . $col . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF666666');
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);


        // Ajustar tamaño de columnas
        foreach (range(0, count($selectedMonths)) as $colIndex) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->getColumnDimension($colLetter)->setWidth(35); // Mitad del tamaño original
            $sheet->getStyle($colLetter)->getAlignment()->setWrapText(true); // Permitir que el texto se corte y pase a otro renglón
        }

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

    /**
     * Convierte un índice numérico en una letra de columna de Excel (A, B, C... AA, AB...)
     */
    private function getColumnLetter($index)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
    }
}
