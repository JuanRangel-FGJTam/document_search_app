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

        // Establecer título del reporte
        $sheet->setCellValue('A1', 'Reporte de Encuestas de '. $start_date. ' al '.$end_date);
        $sheet->mergeCells('A1:' . $this->getColumnLetter(count($data)) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');

        // Definir encabezados de preguntas
        $colIndex = 0;
        foreach ($data as $surveyItem) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->setCellValue($colLetter . '2', $surveyItem['question']);
            $sheet->getStyle($colLetter . '2')->getFont()->setBold(true);
            $sheet->getStyle($colLetter . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colLetter . '2')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9D9D9');
            $colIndex++;
        }

        // Llenar respuestas
        $maxRows = max(array_map(fn($item) => count($item['responses']), $data));
        for ($row = 0; $row < $maxRows; $row++) {
            $colIndex = 0;
            foreach ($data as $surveyItem) {
                $colLetter = $this->getColumnLetter($colIndex);
                $responseValue = $surveyItem['responses'][$row] ?? 'N/A';
                $sheet->setCellValue($colLetter . ($row + 3), $responseValue);
                $colIndex++;
            }
        }

        // Ajustar tamaño de columnas
        foreach (range(0, count($data) - 1) as $colIndex) {
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
                'Content-Disposition' => 'attachment; filename="Encuestas.xlsx"',
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
