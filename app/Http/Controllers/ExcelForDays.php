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
        $this->status_name = $status_name;
        $this->municipality_name = $municipality_name;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 5;

        // Encabezado (centrado y con merge)
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        $sheet->setCellValue('A1', 'Fiscalía General de Justicia del Estado de Tamaulipas');
        $sheet->setCellValue(
            'A2',
            sprintf(
            'Reporte de Solicitudes Por Día - Estado: %s%s - Periodo: %s',
            $status_name ?? 'Todos',
            isset($municipality_name) ? ' - Municipio: ' . $municipality_name : '',
            $start_date && $end_date
                ? sprintf('%s a %s', Carbon::parse($start_date)->format('d/m/Y'), Carbon::parse($end_date)->format('d/m/Y'))
                : ($start_date
                ? Carbon::parse($start_date)->format('d/m/Y')
                : ($end_date ? Carbon::parse($end_date)->format('d/m/Y') : ''))
            )
        );

        // Estilos del encabezado
        $headerStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true // Asegura que el texto largo se ajuste
            ],
            'font' => [
                'bold' => true,
                'size' => 12 // Tamaño un poco más grande
            ]
        ];
        $sheet->getStyle('A1:H3')->applyFromArray($headerStyle);

        // Ajustar altura de filas para el título
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Ajustar ancho de columnas (de A a H) para el título
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setWidth(15); // Ancho uniforme
        }

        // Cabecera de la tabla
        $sheet->setCellValue('A' . $row, 'FECHA');
        $sheet->setCellValue('B' . $row, 'TOTAL');

        // Estilos de la cabecera
        $sheet->getStyle('A' . $row . ':B' . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Eliminar 'total_general' temporalmente para no mostrarlo en la lista de días
        $totalGeneral = $this->data->pull('total_general');
        $dates = $this->data->toArray();

        // Llenar datos por día
        foreach ($dates as $date => $total) {
            $sheet->setCellValue('A' . $row, Carbon::parse($date)->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $total);
            $row++;
        }

        // Agregar fila del TOTAL GENERAL
        $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
        $sheet->setCellValue('B' . $row, $totalGeneral);

        // Estilos del total general
        $sheet->getStyle('A' . $row . ':B' . $row)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF666666');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Ajustar tamaño de columnas
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);

        // Bordes para toda la tabla
        $lastRow = $row;
        $sheet->getStyle('A5:B' . $lastRow)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);

        // Limpiar buffers de salida
        if (ob_get_contents()) ob_end_clean();

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
}
