<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class ExcelForDays
{
    private $data;
    private $status_name;
    private $municipality_name;
    public function create($data, $status_name, $municipality_name, $start_date, $end_date)
    {
        $this->data = $data;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 5;

        // Encabezado
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->setCellValue('A1', 'Fiscalía General de Justicia del Estado de Tamaulipas');
        $sheet->setCellValue(
            'A2',
            sprintf(
                'Reporte de Solicitudes Por Día - Estado: %s%s - Periodo: %s a %s',
                $status_name ?? 'Todos',
                isset($municipality_name) ? ' - Municipio: ' . $municipality_name : '',
                Carbon::parse($start_date)->format('d/m/Y'),
                Carbon::parse($end_date)->format('d/m/Y')
            )
        );

        // Alineación del encabezado
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:J3')->getFont()->setBold(true);

        // Obtener las fechas únicas del reporte
        $dates = array_keys($data->toArray());

        // Primera columna con los tipos de identificación
        $sheet->setCellValue('A' . $row, 'Tipo de Identificación');
        $col = 'B';
        foreach ($dates as $date) {
            $sheet->setCellValue($col . $row, $date);
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
        foreach ($data as $dayData) {
            $identifications = $identifications->merge(collect($dayData)->keys());
        }
        $identifications = $identifications->unique();

        // Llenar la tabla con los datos agrupados
        $dataRows = [];
        foreach ($identifications as $identificationType) {
            $dataRow = ['identification' => $identificationType];
            foreach ($dates as $date) {
                $dataRow[$date] = $data[$date][$identificationType] ?? 0;
            }
            $dataRows[] = $dataRow;
        }

        // Escribir los datos en el Excel
        foreach ($dataRows as $rowData) {
            $sheet->setCellValue('A' . $row, $rowData['identification']);
            $col = 'B';
            foreach ($dates as $date) {
                $sheet->setCellValue($col . $row, $rowData[$date]);
                $col++;
            }
            $row++;
        }

        // Agregar la fila de totales
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $col = 'B';
        foreach ($dates as $date) {
            $totalDay = array_sum(array_column($dataRows, $date));
            $sheet->setCellValue($col . $row, $totalDay);
            $col++;
        }

        // Aplicar estilos a la fila de totales
        $sheet->getStyle('A' . $row . ':' . $col . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF666666');
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A' . $row . ':' . $col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);


        foreach (range(0, count($dates)) as $colIndex) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->getColumnDimension($colLetter)->setWidth(20);
            $sheet->getStyle($colLetter)->getAlignment()->setWrapText(true);
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
                'Content-Disposition' => 'attachment; filename="Reporte_Solicitudes_Diario.xlsx"',
            ]
        );
    }

    private function getColumnLetter($index)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
    }
}
