<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\ {
    PlaceEvent,
    Misplacement,
    IdentificationType,
    People,
    LostStatus,
};
use App\Models\Legacy\{
    Extravio,
    EstadoExtravio,
};

class ExtravioAdapter
{
    public static $DEFAULT_LEGACY_STATUS_ID = 0; // "Especifique"
    public static $DEFAULT_LEGACY_DOCUMENT_TYPE_ID = 6; // "Otro documento"
    public static $DEFAULT_LEGACY_IDENTIFICATION_ID = 5; // "Otro documento"

    /**
     * returns a Extravio legacy model from a local model Misplacement
     *
     * @param  Misplacement $misplacement
     * @return Extravio|null
     */
    public static function fromMisplacement($misplacement, $apiService)
    {
        // * attempt to get the local model realations
        // * get the people
        $people = People::find($misplacement->people_id);
        if($people == null)
        {
            throw new \Exception('People not found for the given Misplacement ID: ' . $misplacement->id);
        }

        // * get place_event
        $placeEvent = PlaceEvent::where('misplacement_id', $misplacement->id)->first();
        if($placeEvent == null)
        {
            throw new \Exception('PlaceEvent not found for the given Misplacement ID: ' . $misplacement->id);
        }

        // * get the status relation
        $lostStatus = LostStatus::find($misplacement->lost_status_id);
        if($lostStatus == null)
        {
            throw new \Exception('LostStatus not found for the given Misplacement ID: ' . $misplacement->id);
        }

        // * get identification type
        $misplacement->load('misplacementIdentifications');
        $identificationType = IdentificationType::find($misplacement->misplacementIdentifications->identification_type_id);
        if($identificationType == null)
        {
            throw new \Exception('IdentificationType not found for the given Misplacement ID: ' . $misplacement->id);
        }

        // * create the legacy Extravio object
        $extravio = new Extravio();
        $extravio->ID_EXTRAVIO = trim($misplacement->document_number);
        $extravio->DESCRIPCION = $misplacement->observations;
        // Updated to convert lost_date to full datetime with milliseconds
        $extravio->FECHA_EXTRAVIO = Carbon::parse(trim($placeEvent->lost_date))->format('Y-m-d');
        $extravio->FECHA_REGISTRO = Carbon::parse($misplacement->registration_date)->format('Y-m-d H:i:s.v');
        $extravio->FECHA_CANCELACION = null;
        $extravio->ACTIVO = 1;

        // * deserialize the name
        $nameParts = explode(' ', trim($people->name));
        switch (count($nameParts)) {
            case 1:
                $extravio->NOMBRE = trim($people->name);
                break;
            case 2:
                $extravio->NOMBRE = $nameParts[0] ?? null;
                $extravio->PATERNO = $nameParts[1] ?? null;
                $extravio->MATERNO = null;
            case 3:
                $extravio->NOMBRE = $nameParts[0] ?? null;
                $extravio->PATERNO = $nameParts[1] ?? null;
                $extravio->MATERNO = $nameParts[2] ?? null;
                break;
            default:
                $extravio->NOMBRE = implode(' ', array_slice($nameParts, 0, -2));
                $extravio->PATERNO = $nameParts[count($nameParts) - 2] ?? null;
                $extravio->MATERNO = $nameParts[count($nameParts) - 1] ?? null;
                break;
        }

        // * attempt to get the legacy status
        $legacyEstadoExtravio = EstadoExtravio::where('ESTADO_EXTRAVIO', 'LIKE', '%' . Str::upper($lostStatus->name) . '%')->first();
        $extravio->ID_ESTADO_EXTRAVIO = isset($legacyEstadoExtravio)
            ? $legacyEstadoExtravio->ID_ESTADO_EXTRAVIO
            : self::$DEFAULT_LEGACY_STATUS_ID;


        // * set the cancelation info
        if($misplacement->cancellation_date)
        {
            $extravio->FECHA_CANCELACION = Carbon::parse($misplacement->cancellation_date)->format('Y-m-d H:i:s.v');
            $extravio->OBSERVACIONES_CANCELACION = $misplacement->cancellation_reason_description;
            $extravio->ID_MOTIVO_CANCELACION = $misplacement->cancellation_reason_id;  
        }

        $api_identification = $apiService->getIdentificationByType($misplacement->people_id, $identificationType->id);
        if($api_identification)
        {
            $extravio->ID_IDENTIFICACION = $api_identification['id'] ?? null;
            $extravio->NUMERO_DOCUMENTO = $api_identification['folio'] ?? null;
            $extravio->ESPECIFIQUE = $api_identification['documentTypeName'] ?? null;
        }

        // * other fields
        // $extravio->ACTIVO = 1;
        // $extravio->CODIGO = 0; // Codigo utilizada on QR
        // $extravio->CODIGO_CORTO = 0; // Codigo QR
        // $extravio->CODIGO_COMPLETO = 0; // Codigo QR

        // $extravio->IDENTIFICACION = null; // BLOB
        // $extravio->IDENTIFICACION_DESC = null; // NOMBRE DE ARCHIVO
        
        // $extravio->idUsuario = 1;
        // $extravio->ExtravioPlaca = null;

        return $extravio;

    }

}
