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

        // Agregar logo
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('/images/logos/logo_fgjtam.png')); // Cambiar por la ruta del logo
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);

        // Establecer título del reporte
        $sheet->setCellValue('B1', 'Encuesta de satisfacción de ' . $start_date . ' al ' . $end_date);
        $sheet->mergeCells('B1:' . $this->getColumnLetter(count($data[0])) . '1');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');

        // Definir encabezados de preguntas
        $colIndex = 0;
        foreach (array_keys($data[0]) as $question) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->setCellValue($colLetter . '2', $question);
            $sheet->getStyle($colLetter . '2')->getFont()->setBold(true);
            $sheet->getStyle($colLetter . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colLetter . '2')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9D9D9');
            $colIndex++;
        }

        // Llenar respuestas
        $rowIndex = 3;
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
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Descargar el archivo
        $writer = new Xlsx($spreadsheet);
        if (ob_get_contents()) ob_end_clean(); // Limpiar buffer de salida

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
