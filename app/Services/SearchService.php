<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\ViewModels\SearchResult;
use App\Models\Vehicle;
use App\Services\AuthApiService;

class SearchService
{
    protected AuthApiService $authApiService;

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

        $response = [];

        // * retrive vehicle info
        $vehicles = Vehicle::whereIn('id', $vehiclesId)
            ->with(['misplacement', 'vehicleBrand', 'vehicleSubBrand', 'plateType', 'vehicleModel'])
            ->get();

        foreach($vehicles as $vehicle)
        {
            $model = new SearchResult();
            $model->id = $vehicle->id;
            $model->plateNumber = $vehicle->plate_number;
            $model->personId = $vehicle->misplacement->people_id;
            $model->registerDate = now()->format("Y-m-d H:i:s");
            $model->vehicle = $vehicle->toArray();

            // * retrive person data from API
            $person = $this->authApiService->getPersonById($model->personId);
            $model->person = $person;

            $model->fullName = $person ? $person['fullName'] : "*No disponible";

            $response[] = $model;
        }
        
        return $response;
    }
}
