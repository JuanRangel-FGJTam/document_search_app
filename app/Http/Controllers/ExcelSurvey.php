<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class ExcelSurvey
{
    public function create($data, $start_date, $end_date)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar logo con más separación
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('/images/logos/logo_fgjtam.png'));
        $drawing->setHeight(70);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);

        // Reservar más espacio para el logo (fusionar varias celdas verticalmente)
        $sheet->mergeCells('A1:A3');

        $mx_start_date = Carbon::parse($start_date)->format('d/m/Y');
        $mx_end_date = Carbon::parse($end_date)->format('d/m/Y');

        // Establecer título del reporte sin fondo y más grande
        $sheet->mergeCells('C1:P3');
        $sheet->setCellValue('C1', 'Encuestas de satisfacción del ' . $mx_start_date . ' al ' . $mx_end_date);
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        if (isset($data[0])) {
            // Definir encabezados de preguntas con fondo azul rey y letras blancas
            $sheet->setCellValue('A6', '#');
            $sheet->getStyle('A6:P6')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF'); // Letras blancas
            $sheet->getStyle('A6:P6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A6:P6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A6:P6')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FF0b142e'); //  #0b142e 
            $sheet->getColumnDimension('A')->setWidth(5);
            $colIndex = 0;

            foreach (array_keys($data[0]) as $question) {
                $colLetter = $this->getColumnLetter($colIndex + 1);
                $sheet->setCellValue($colLetter . '6', $question);
                $sheet->getColumnDimension($colLetter)->setWidth(23); // Hacer los encabezados más largos
                $colIndex++;
            }

            // Llenar respuestas
            $rowIndex = 7;
            foreach ($data as $surveyItem) {
                $sheet->setCellValue('A' . $rowIndex, $rowIndex - 6);

                $colIndex = 0;
                foreach ($surveyItem as $response) {
                    $colLetter = $this->getColumnLetter($colIndex + 1);
                    $sheet->setCellValue($colLetter . $rowIndex, $response);
                    $colIndex++;
                }
                $rowIndex++;
            }

            $sheet->getStyle('A6:P6')->getAlignment()->setWrapText(true);
        } else {
            $sheet->mergeCells('A5:P5');
            $sheet->setCellValue('A5', 'No hay encuestas en el periodo seleccionado');
            $sheet->getStyle('A5')->getFont()->setSize(12);
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD3D3D3');            
        }

        // Descargar el archivo
        $writer = new Xlsx($spreadsheet);
        if (ob_get_contents()) ob_end_clean();

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="Encuesta_Satisfaccion.xlsx"',
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
