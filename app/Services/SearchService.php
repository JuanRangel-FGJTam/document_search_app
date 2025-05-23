<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ViewModels\SearchResult;
use App\Services\AuthApiService;
use App\Models\{
    Vehicle,
    SearchPermission
};

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
     * @param mixed $search
     * @return array<SearchResult>
     */
    public function search(string $search)
    {
        Log::info("Searching plate_numbers for [{searchParam}]", [
            "searchParam" => $search
        ]);

        // * search the plate_numbers
        $vehiclesId = Vehicle::where('plate_number', 'like', '%' . $search . '%')->pluck('id');
         Log::info("Search completed for plate_numbers", [
            "searchParam" => $search,
            "result_count" => count($vehiclesId)
        ]);

        // * retrive vehicle info
        $vehicles = Vehicle::whereIn('id', $vehiclesId)
            ->with(['misplacement', 'misplacement.lostStatus', 'misplacement.placeEvent', 'vehicleBrand', 'vehicleSubBrand', 'plateType', 'vehicleModel', 'vehicleType'])
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
     * processResponse
     *
     * @param  mixed $vehicles
     * @return array
     */
    private function processResponse($vehicles)
    {
        $response = [];

        // * get all the permissions
        $permissions = SearchPermission::select('id', 'name')->pluck('name', 'id')->toArray();

        // * Get the current user permission if is external user
        $user = auth()->user();
        if($user->external_user_id)
        {
            $user->load(['externalUser']);
            $permissions = $user->externalUser->currentPermissions();
        }

        // * process each vehicle and adapt the info
        foreach($vehicles as $vehicle)
        {
            $_vehicleId = $vehicle->id;
            $_documentNumber = $vehicle->misplacement->document_number;

            $model = new SearchResult($_documentNumber, $_vehicleId);
            $model->plateNumber = $vehicle->plate_number;
            $model->serialNumber = $vehicle->serie_number;
            $model->personId = $vehicle->misplacement->people_id;
            $model->registerDate = now()->format("Y-m-d H:i:s");

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

            $response[] = $model;
        }

        return $response;
    }

}
