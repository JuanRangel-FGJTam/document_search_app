<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $index = 0;

        // Encabezado de la empresa y dirección
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');
        $sheet->getRowDimension(1)->setRowHeight(23);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);

        $sheet->setCellValue('A1', 'Fiscalía General de Justicia del Estado de Tamaulipas');
        $sheet->setCellValue('A2', 'Dirección General de Tecnología, Información y Telecomunicaciones');

        // Alineación del encabezado
        $sheet->getStyle('A1:J3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:J3')->getFont()->setBold(true);

        // Establecer encabezados de columnas (mes, total solicitudes, y tipo de identificación)
        $headers = ['Mes', 'Total Solicitudes', 'Tipo de Identificación', 'Cantidad'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . $row, $header);
            $sheet->getStyle($column . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF020255');
            $sheet->getStyle($column . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $sheet->getStyle($column . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle($column . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle($column . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getColumnDimension($column)->setWidth(20); // Ajustar el ancho de las columnas
            $column++;
        }
        $row++;

        // Agregar los datos con meses en español
        foreach ($this->data['data'] as $month => $monthData) {
            // Convertir el mes a español con Carbon
            $monthName = Carbon::parse($month)->locale('es')->isoFormat('MMMM');

            // Agregar mes y total de solicitudes
            $sheet->setCellValue('A' . $row, ucfirst($monthName)); // Usamos ucfirst para que el mes empiece con mayúscula
            $sheet->setCellValue('B' . $row, $monthData['total_solicitudes']);
            $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;

            // Agregar tipo de identificación y su cantidad
            foreach ($monthData['identifications_count'] as $identificationType => $count) {
                $sheet->setCellValue('C' . $row, $identificationType);
                $sheet->setCellValue('D' . $row, $count);
                $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $row++;
            }
        }

        // Agregar mensaje final
        $row++;
        $sheet->mergeCells('A' . $row . ':J' . $row);
        $sheet->setCellValue('A' . $row, 'Toda la información contenida en este reporte está protegida por las leyes de privacidad y confidencialidad aplicables. Cualquier divulgación no autorizada, uso indebido o reproducción de esta información está estrictamente prohibida y puede estar sujeta a sanciones administrativas y/o legales.');
        $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);

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
