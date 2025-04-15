<?php

namespace App\Services;

use App\Models\Legacy\Extravio;
use App\Models\Legacy\Objeto;
use App\Services\AuthApiService;

class MisplacementLegacyService
{
    public function getPersonLegacy(string $misplacement_id)
    {
        $authApiService = new AuthApiService();
        $extravio = Extravio::where('ID_EXTRAVIO', $misplacement_id)->first();
        $extravio->load('identificacion.cat_identificacion', 'identificacion.cat_municipio', 'identificacion.cat_localidad', 'hechos', 'hechosCP', 'domicilioCP');

        $person = null;
        if ($extravio->usuario && $extravio->usuario->idPersonApi) {
            $person = $authApiService->getPersonById($extravio->usuario->idPersonApi);
        }

        if (!$person) {
            $name = '';

            if ($extravio->NOMBRE && $extravio->PATERNO && $extravio->MATERNO) {
                $name = $extravio->NOMBRE . ' ' . $extravio->PATERNO . ' ' . $extravio->MATERNO;
            }

            if (empty($name) && $extravio->identificacion) {
                $name = $extravio->identificacion->NOMBRE . ' ' . $extravio->identificacion->PATERNO . ' ' . $extravio->identificacion->MATERNO;
            }
            $person = [
                'fullName' => $name,
                'curp' => $extravio->identificacion->curprfc,
                'genderName' => $extravio->identificacion->ID_SEXO === "1" ? "Masculino" : "Femenino",
                'birthdateFormated' => $extravio->identificacion->FECHA_NACIMIENTO,
                'age' => \Carbon\Carbon::parse($extravio->identificacion->FECHA_NACIMIENTO)->age,
                'email' => $extravio->identificacion->CORREO_ELECTRONICO,
                'address' => [
                    'municipalityName' => $extravio->domicilioCP->CPmunicipio,
                    'colonyName' => $extravio->domicilioCP->CPcolonia,
                    'street' => $extravio->domicilioCP->CPcalle,
                ],
                'identificacion'=> [
                    'documentTypeName'=> $extravio->identificacion->cat_identificacion->IDENTIFICACION,
                    'folio'=>$extravio->identificacion->NUMERO_IDENTIFICACION,
                    'documentTypeId'=> $extravio->identificacion->ID_TIPO_IDENTIFICACION,
                    'valid'=> null,
                ]
            ];

        }

        if ($extravio->identificacion->IDENTIFICACION) {
            $personId = $extravio->usuario->idPersonApi ?? $misplacement_id;
            $filename = 'identification_' . $personId . '.jpg';
            $path = 'public/identifications/' . $filename;
            \Illuminate\Support\Facades\Storage::put($path, $extravio->identificacion->IDENTIFICACION);
            $person['identificacion']['file_path'] = 'storage/identifications/' . $filename;
        }

        return $person;
    }

    public function getPlaceData(string $misplacement_id)
    {
        $extravio = Extravio::where('ID_EXTRAVIO', $misplacement_id)->first();
        $extravio->load('hechos', 'hechosCP');
        return [
            'municipality_name' => $extravio->hechosCP->CPmunicipio ?? null,
            'colony_name' => $extravio->hechosCP->CPcolonia ?? null,
            'street' => $extravio->hechosCP->CPcalle ?? null,
            'lost_date' => $extravio->hechos->FECHA ?? null,
            'description' => $extravio->hechos->DESCRIPCION ?? null
        ];
    }

    public function getLostDocuments(string $misplacement_id){
        $documentsData = Objeto::where('ID_EXTRAVIO', $misplacement_id)->get();
        $documentsData->load('tipoDocumento');

        $documents = $documentsData->map(function ($doc) {
            return [
                'id' => $doc->ID_OBJETO,
                'document_type' =>  $doc->tipoDocumento->DOCUMENTO,
                'document_type_id' => ($doc->ID_TIPO_DOCUMENTO === 5 || $doc->ID_TIPO_DOCUMENTO === 6) ? 9 : $doc->ID_TIPO_DOCUMENTO,
                'document_number' => $doc->NUMERO_DOCUMENTO,
                'document_owner' => $doc->TITULAR_DOCUMENTO,
                'specification'=> $doc->ESPECIFIQUE
            ];
        });

        return $documents;
    }
}
