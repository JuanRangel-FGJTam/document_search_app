<?php

namespace App\Helpers;

use App\Models\ {
    DocumentType,
    LostDocument
};
use App\Models\Legacy\{
    Objeto,
    TipoDocumento
};

class ExtravioObjectAdapter
{

    public static $DEFAULT_LEGACY_DOCUMENT_TYPE_ID = 6; // Otro documento

    /**
     * returns a Objeto legacy model from a local model LostDocument
     *
     * @param  LostDocument $misplacement
     * @return Objeto|null
     */
    public static function fromLostDocument($lostDocument)
    {
        // * get the local relations
        $documentType = DocumentType::find($lostDocument->document_type_id);

        $objeto = new Objeto();
        // $objeto->ID_EXTRAVIO = $extravio->ID_EXTRAVIO;
        $objeto->NUMERO_DOCUMENTO = $lostDocument->document_number;
        $objeto->TITULAR_DOCUMENTO = $lostDocument->document_owner;
        $objeto->DESCRIPCION = $lostDocument->specification;
        $objeto->FECHA_REGISTRO = trim($lostDocument->registration_date) . " 00:00:00.000";
        $objeto->ACTIVO = $lostDocument->active;


        // * get the legacy document type
        $legacyTipoDocumento = TipoDocumento::where('DOCUMENTO', 'like', $documentType->name)->first();
        if ($legacyTipoDocumento != null) {
            $objeto->ID_TIPO_DOCUMENTO = $legacyTipoDocumento->ID_TIPO_DOCUMENTO;
        } else {
            $objeto->ID_TIPO_DOCUMENTO = self::$DEFAULT_LEGACY_DOCUMENT_TYPE_ID;
            $objeto->ESPECIFIQUE = $documentType->name ?? "null";
        }

        // * other fields
        // $objeto->IDMARCA = "";
        // $objeto->IDTIPO = 0;
        // $objeto->IDMODELO = 0;
        // $objeto->NUMEROSERIE = "";

        return $objeto;
    }

}
