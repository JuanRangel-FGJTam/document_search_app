<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ViewModels\SearchResult;
use App\Services\AuthApiService;
use App\Models\{
    Vehicle,
    SearchPermission
};
use App\Helpers\SearchTypes;

class SearchService
{
    protected AuthApiService $authApiService;

    protected static $MOSTRAR_PERSONA = 1;
    protected static $MOSTRAR_VEHICULO = 2;

    public function __construct(AuthApiService $authApiService)
    {
        $this->authApiService = $authApiService;
    }

    /**
     * search
     *
     * @param string $search
     * @param ?string $type
     * @return array<SearchResult>
     */
    public function search(string $search, $type)
    {
        Log::info("Searching plate_numbers for [{searchParam}]", [
            "searchParam" => $search
        ]);

        $searchType = $type ?? SearchTypes::$PLACA;

        // * search in local db
        $response = $this->searchLocal($search, $type);

        // * search in legacy db
        if($searchType == SearchTypes::$PLACA)
        {
            $responseLegacy = $this->searchLegacy($search);
            $response = array_merge($response, $responseLegacy);
        }

        Log::info("Search completed for plate_numbers", [
            "searchParam" => $search,
            "searchType" => $searchType,
            "result_count" => count($response)
        ]);

        return $response;
    }

    /**
     * finByVehicleId
     *
     * @param int $vehicleId
     * @return SearchResult
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function finByVehicleId(int $vehicleId)
    {
        // * retrive vehicle info
        $vehicle = Vehicle::with(['misplacement', 'misplacement.lostStatus', 'misplacement.placeEvent', 'vehicleBrand', 'vehicleSubBrand', 'plateType', 'vehicleModel', 'vehicleType'])
            ->findOrFail($vehicleId);

        // * process each vehicle and adapt the info
        $response = $this->processResponse([$vehicle])[0];
        return $response;
    }

    /**
     * finByVehicleId
     *
     * @param int $vehicleId
     * @return SearchResult
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function findLigacyByObjectoId($idObjeto)
    {
        // * retrive vehicle info
        $sql = File::get(resource_path('sql/get-plate-legacy.sql'));

        // * fetch the query
        $results = DB::connection('sqlsrv')->select($sql, ['IdObjeto' => $idObjeto]);
        if( empty($results) )
        {
            abort(404, "Registro no encontrado en la base legacy");
        }

        // * get the permissions
        $permissions = $this->getUserPermissions();

        // * process the data
        return $this->processResponseLegacy($results[0], $permissions);
    }

    #region private functions
    /**
     * searchLocal
     *
     * @param  string $searchKeyWord
     * @param  string $type
     * @return array<SearchResponse>
     */
    private function searchLocal($searchKeyWord, $type)
    {
        $searchType = $type ?? SearchTypes::$PLACA;
        $vehiclesId = [];

        switch ($searchType) {
            case SearchTypes::$SERIAL_NUMBER:
                $vehiclesId = Vehicle::where('serie_number', 'like', '%' . $searchKeyWord . '%')->pluck('id');
                break;

            case SearchTypes::$DOCUMENT_NUMBER:
                $vehiclesId = Vehicle::whereHas('misplacement', fn($missp) => $missp->where('document_number', 'like', '%' . $searchKeyWord . '%'))->pluck('id');
                break;

            default: // * search the plate_numbers
                $vehiclesId = Vehicle::where('plate_number', 'like', '%' . $searchKeyWord . '%')->pluck('id');
                break;
        }

        // * retrive vehicle info
        $vehicles = Vehicle::whereIn('id', $vehiclesId)
            ->with(['misplacement', 'misplacement.lostStatus', 'misplacement.placeEvent', 'vehicleBrand', 'vehicleSubBrand', 'plateType', 'vehicleModel', 'vehicleType'])
            ->get();

        // * process each vehicle and adapt the info
        $response = $this->processResponse($vehicles);
        return $response;
    }

    /**
     * searchLegacy
     *
     * @param  string $searchKeyWord
     * @return array<SearchResponse>
     */
    private function searchLegacy($searchKeyWord)
    {
        $searchResponses = [];

        // * loas the custom query
        $sql = File::get(resource_path('sql/search-plate-legacy.sql'));

        // * fetch the query
        $results = DB::connection('sqlsrv')->select($sql, ['plateNumber' => '%'. $searchKeyWord . '%']);
        if( empty($results) )
        {
            return [];
        }

        // * get all the permissions
        $permissions = $this->getUserPermissions();

        foreach($results as $s)
        {
            $searchResponses[] = $this->processResponseLegacy($s, $permissions);
        }

        return $searchResponses;
    }

    /**
     * processResponse
     *
     * @param  mixed $vehicles
     * @return array
     */
    private function processResponse($vehicles)
    {
        $response = [];

        // * get all the permissions
        $permissions = $this->getUserPermissions();

        // * process each vehicle and adapt the info
        foreach($vehicles as $vehicle)
        {
            $_vehicleId = $vehicle->id;
            $_documentNumber = $vehicle->misplacement->document_number;

            $model = new SearchResult($_documentNumber, $_vehicleId);
            $model->plateNumber = $vehicle->plate_number;
            $model->serialNumber = $vehicle->serie_number;
            $model->personId = $vehicle->misplacement->people_id;
            $model->registerDate = Carbon::parse($vehicle->created_at)->format("Y-m-d H:i:s");

            // * check if has the permision for show the vehicle
            if (array_key_exists(self::$MOSTRAR_VEHICULO, $permissions)) {
                $model->setVehicle($vehicle);
            }

            $misplacement = $vehicle->misplacement;
            $model->setMisplacement($misplacement);

            // * retrive person data from API
            $person = $this->authApiService->getPersonById($model->personId);
            // * check if has the permision for show the person
            if (array_key_exists(self::$MOSTRAR_PERSONA, $permissions)) {
                $model->setPerson($person);
                $model->fullName = $person['fullName'] ?? "*No disponible";
            }
            else
            {
                $model->fullName = \App\Helpers\HideText::hide($person['fullName']);
            }

            // * set placeEvent
            try {
                $vehicle->misplacement->load('placeEvent');
                $placeEvent = $vehicle->misplacement->placeEvent;
                if ($placeEvent) {
                    $zipcodeData = $this->authApiService->getZipCode($placeEvent->zipcode);
                    $model->setPlaceEvent($placeEvent, $zipcodeData);
                }
            } catch (\Throwable $th) {
                Log::error("Fail to load the place event of {folio}: {message}", [
                    "folio" => $model->documentNumber,
                    "message" => $th->getMessage()
                ]);
            }

            $response[] = $model;
        }

        return $response;
    }

    /**
     * processResponseLegacy
     *
     * @param  mixed $legacyData
     * @param  array<SearchPermission> $permissions
     * @return SearchResult
     */
    private function processResponseLegacy($legacyData, $permissions)
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

        if (array_key_exists(self::$MOSTRAR_PERSONA, $permissions)) {
            $model->fullName = $name;
        }
        else
        {
            $model->fullName = \App\Helpers\HideText::hide($name);
        }

        return $model;
    }

    private function getUserPermissions()
    {
        // * get all the permissions
        $permissions = SearchPermission::select('id', 'name')->pluck('name', 'id')->toArray();

        // * Get the current user permission if is external user
        $user = auth()->user();
        if($user->external_user_id)
        {
            $user->load(['externalUser']);
            $permissions = $user->externalUser->currentPermissions();
        }
        return $permissions;
    }
    #endregion

}