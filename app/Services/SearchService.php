<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ViewModels\SearchResult;
use App\Services\AuthApiService;
use App\Contracts\MisplacementRepositoryInterface;
use App\Repositories\
{
    LegacyMisplacementRepository,
    LostDocumentRepository
};
use App\Models\{
    Vehicle,
    SearchPermission
};
use App\Helpers\SearchTypes;

class SearchService
{
    protected AuthApiService $authApiService;

    protected MisplacementRepositoryInterface $legacyRepo;
    protected MisplacementRepositoryInterface $lostDocumentRepo;


    protected static $MOSTRAR_PERSONA = 1;
    protected static $MOSTRAR_VEHICULO = 2;

    public static int $SOURCE_LOCAL = 1;
    public static int $SOURCE_LEGACY = 2;
    public static int $SOURCE_LOSDOCUMENT = 3;

    public static int $DOCUMENT_STATUS_VALIDATED = 3;

    public static int $CHUNK_SIZE= 50;

    public function __construct(AuthApiService $authApiService)
    {
        $this->authApiService = $authApiService;
        $this->legacyRepo = new LegacyMisplacementRepository();
        $this->lostDocumentRepo = new LostDocumentRepository($this->authApiService);
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

        // * search from the previous app
        // $lostDocumentResponse = $this->lostDocumentRepo->search(
        //     $search,
        //     permissions: $this->getUserPermissions(),
        //     type: $searchType
        // );
        // $response = array_merge($response, $lostDocumentResponse);

        // * search from the other origins
        // if($searchType == SearchTypes::$PLACA)
        // {
        //     $responseLegacy = $this->legacyRepo->search(
        //         $search,
        //         permissions: $this->getUserPermissions(),
        //         type: $searchType
        //     );
        //     $response = array_merge($response, $responseLegacy);
        // }

        Log::info("Search completed for plate_numbers", [
            "searchParam" => $search,
            "searchType" => $searchType,
            "result_count" => count($response)
        ]);

        return $response;
    }

    /**
     * search
     *
     * @param string $search
     * @param ?string $type
     * @return array<SearchResult>
     */
    public function getData($hasCredential, $vehicleType, $month, $year, $page = 0 )
    {
        Log::info("Searching plate_numbers for [{searchParam}] with filters", [
            "hasCredential" => $hasCredential,
            "vehicleType" => $vehicleType,
            "month" => $month,
            "year" => $year
        ]);

        // * retrive vehicle info
        $vehicles = Vehicle::with(['misplacement', 'misplacement.lostStatus', 'misplacement.placeEvent', 'vehicleBrand', 'vehicleSubBrand', 'plateType', 'vehicleModel', 'vehicleType'])
            ->whereHas('misplacement', fn($m) => $m->where('lost_status_id',self::$DOCUMENT_STATUS_VALIDATED))
            // ->when(($hasCredential ?? 0) > 0, function($query) use($hasCredential){
            //     return $query->when($hasCredential == 1, fn()'vehicle_type_id', $vehicleType);
            // })
            ->when(($vehicleType ?? 0) > 0, fn($query) => $query->where('vehicle_type_id', $vehicleType) )
            ->whereHas('misplacement', function($mis) use($year, $month){
                return $mis->whereYear('registration_date', $year)->whereMonth('registration_date', $month);
            })
            ->orderBy('created_at', 'desc')
            ->skip($page * self::$CHUNK_SIZE)
            ->take(self::$CHUNK_SIZE)
            ->get();

        // * process each vehicle and adapt the info
        $response = $this->processResponse($vehicles);

        return $response;
    }


    /**
     * finByVehicleId
     *
     * @param int $vehicleId
     * @return SearchResult
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function finByVehicleId(int $vehicleId, ?int $source = null)
    {
        // if($source == SELF::$SOURCE_LOSDOCUMENT)
        // {
        //     return $this->lostDocumentRepo->findByVehicleId($vehicleId, $this->getUserPermissions());
        // }

        // if($source == SELF::$SOURCE_LEGACY)
        // {
        //     return $this->legacyRepo->findByVehicleId($vehicleId, $this->getUserPermissions());
        // }

        // * retrive vehicle info
        $vehicle = Vehicle::with(['misplacement', 'misplacement.lostStatus', 'misplacement.placeEvent', 'vehicleBrand', 'vehicleSubBrand', 'plateType', 'vehicleModel', 'vehicleType'])
            ->findOrFail($vehicleId);

        // * process each vehicle and adapt the info
        $response = $this->processResponse([$vehicle])[0];
        return $response;
    }


    #region private functions
    /**
     * searchLocal
     *
     * @param  string $searchKeyWord
     * @param  string $type
     * @return array<SearchResponse>
     */
    private function searchLocal($searchKeyWord, $searchType)
    {
        $searchType = $searchType ?? SearchTypes::$PLACA;
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
            ->whereHas('misplacement', fn($m) => $m->where('lost_status_id',self::$DOCUMENT_STATUS_VALIDATED))
            ->get();

        // * process each vehicle and adapt the info
        $response = $this->processResponse($vehicles);
        return $response;
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