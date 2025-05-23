<?php

namespace App\Helpers;

class SearchTypes
{
    public static string $PLACA = "plate_number";
    public static string $SERIAL_NUMBER = "serie_number";
    public static string $DOCUMENT_NUMBER = "document_number";

    public static array $types = [
        "plate_number" => "Placa",
        "serie_number" => "Numero de Serie",
        "document_number" => "Folio de Reporte"
    ];
}