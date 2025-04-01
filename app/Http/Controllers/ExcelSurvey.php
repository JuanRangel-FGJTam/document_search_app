<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

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

        // Establecer título del reporte sin fondo y más grande
        $sheet->setCellValue('B3', 'Encuesta de satisfacción de ' . $start_date . ' al ' . $end_date);
        $sheet->mergeCells('B3:' . $this->getColumnLetter(count($data[0])) . '3');
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Definir encabezados de preguntas con fondo azul rey y letras blancas
        $colIndex = 0;
        foreach (array_keys($data[0]) as $question) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->setCellValue($colLetter . '6', $question);
            $sheet->getStyle($colLetter . '6')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF'); // Letras blancas
            $sheet->getStyle($colLetter . '6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colLetter . '6')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF000080'); // Azul rey
            $sheet->getColumnDimension($colLetter)->setWidth(70); // Hacer los encabezados más largos
            $colIndex++;
        }

        // Llenar respuestas
        $rowIndex = 7;
        foreach ($data as $surveyItem) {
            $colIndex = 0;
            foreach ($surveyItem as $response) {
                $colLetter = $this->getColumnLetter($colIndex);
                $sheet->setCellValue($colLetter . $rowIndex, $response);
                $colIndex++;
            }
            $rowIndex++;
        }

        // Ajustar tamaño de columnas
        foreach (range(0, count($data[0]) - 1) as $colIndex) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->getColumnDimension($colLetter)->setWidth(35); // Mitad del tamaño original
            $sheet->getStyle($colLetter)->getAlignment()->setWrapText(true); // Permitir que el texto se corte y pase a otro renglón
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
