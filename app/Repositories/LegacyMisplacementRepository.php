<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use App\Contracts\MisplacementRepositoryInterface;
use App\Helpers\SearchTypes;
use App\Models\SearchPermission;
use App\ViewModels\SearchResult;

class LegacyMisplacementRepository implements MisplacementRepositoryInterface
{
    protected static $MOSTRAR_PERSONA_PERMISSION = 1;
    
    public function search(string $search, array $permissions, ?string $type = null)
    {
        // * Force to search only by `Placa`
        if($type != null && $type !== \App\Helpers\SearchTypes::$PLACA)
        {
            return [];
        }

        $searchResponses = [];

        // * load custom query
        $sql = File::get(resource_path('sql/legacy/legacy-search-plate.sql'));

        // * fetch the query
        $results = DB::connection('sqlsrv')->select($sql, ['plateNumber' => '%'. $search . '%']);
        if( empty($results) )
        {
            return [];
        }

        // * process the response
        foreach($results as $s)
        {
            $searchResponses[] = $this->processResponse($s, $permissions);
        }

        return $searchResponses;
    }

    public function findByVehicleId(string|int $idObjeto, array $permissions)
    {
        // * retrive vehicle info
        $sql = File::get(resource_path('sql/legacy/legacy-get-plate.sql'));

        // * fetch the query
        $results = DB::connection('sqlsrv')->select($sql, ['IdObjeto' => $idObjeto]);
        if( empty($results) )
        {
            abort(404, "Registro no encontrado en la base legacy");
        }

        // * process the data
        return $this->processResponse($results[0], $permissions);
    }

    /**
     * processResponseLegacy
     *
     * @param  mixed $legacyData
     * @param  array<SearchPermission> $permissions
     * @return SearchResult
     */
    private function processResponse($legacyData, $permissions)
    {
        $_vehicleId = "L" . $legacyData->ID_OBJETO;
        $_documentNumber = "L" . $legacyData->ID_EXTRAVIO;

        $model = new SearchResult($_documentNumber, $_vehicleId);
        $model->plateNumber = $legacyData->NUMERO_DOCUMENTO;
        $model->serialNumber = "";
        $model->registerDate = Carbon::parse($legacyData->FECHA_REGISTRO)->format("Y-m-d");

        $model->setLegacyMisplacement($legacyData);
        $model->setLegacyPlaceEvent($legacyData);

        // * retrive name
        $name = trim(implode(' ', [
            $legacyData->NOMBRE,
            $legacyData->PATERNO,
            $legacyData->MATERNO,
        ]));

        // * if name is empty override with titular
        if (empty($name))
        {
            $name = $legacyData->TITULAR_DOCUMENTO;
        }

        if (array_key_exists(self::$MOSTRAR_PERSONA_PERMISSION, $permissions)) {
            $model->fullName = $name;
        }
        else
        {
            $model->fullName = \App\Helpers\HideText::hide($name);
        }

        return $model;
    }

}