<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use App\Contracts\MisplacementRepositoryInterface;
use App\Helpers\SearchTypes;
use App\Models\SearchPermission;
use App\Services\AuthApiService;
use App\ViewModels\SearchResult;
use App\ViewModels\SearchResultMisplacement;
use App\ViewModels\SearchResultPlaceEvent;

class LostDocumentRepository implements MisplacementRepositoryInterface
{
    protected static $MOSTRAR_PERSONA_PERMISSION = 1;
    
    protected AuthApiService $authApiService;

    public function __construct(AuthApiService $authApiService)
    {
        $this->authApiService = $authApiService;
    }
    
    public function search(string $search, array $permissions, ?string $type = null)
    {
        // * Prevent search by serial_number
        if($type === \App\Helpers\SearchTypes::$SERIAL_NUMBER)
        {
            return [];
        }

        $searchResponses = [];

        // * load custom query
        $sql = File::get(resource_path('sql/lost_document/lost_document_search_plate.sql'));

        // * fetch the query
        $results = DB::select($sql, [
            'searchType1' => $type == \App\Helpers\SearchTypes::$PLACA ? 1: 2,
            'searchType2' => $type == \App\Helpers\SearchTypes::$PLACA ? 1: 2,
            'plateNumber1' => '%'. $search . '%',
            'plateNumber2' => '%'. $search . '%',
        ]);
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

    public function findByVehicleId(string|int $documentId, array $permissions)
    {
        // * retrive vehicle info
        $sql = File::get(resource_path('sql/lost_document/lost_document_get_plate.sql'));

        // * fetch the query
        $results = DB::select($sql, ['documentNumber' => $documentId]);
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
    private function processResponse($data, $permissions)
    {
        $_vehicleId = "D" . $data->document_id;
        $_documentNumber = "D" . $data->document_number;

        $model = new SearchResult($_documentNumber, $_vehicleId);
        $model->plateNumber = $data->plate_number;
        $model->serialNumber = "";
        $model->registerDate = Carbon::parse($data->registration_date)->format("Y-m-d");

        // * create misplacement
        $misplaceMent = new SearchResultMisplacement();
        $misplaceMent->documentNumber = $data->document_number;
        $misplaceMent->statusId = $data->lost_status_id;
        $misplaceMent->statusName = $data->status_name;
        $model->misplacement = $misplaceMent;

        // * create place event
        $placeEvent = new SearchResultPlaceEvent();
        $placeEvent->lostDate = $data->lost_date;
        $placeEvent->zipCode = $data->zipcode;
        $placeEvent->municipalityId = $data->municipality_id;
        $placeEvent->municipalityName = "*No disponible";
        $placeEvent->colonyId = $data->colony_id;
        $placeEvent->colonyName = "*No disponible";
        $placeEvent->street = $data->street;
        $placeEvent->description = $data->description;
        // * retrive zipcode data from api
        try {
            $zipcodeData = $this->authApiService->getZipCode($placeEvent->zipCode);
            $municipalities = $zipcodeData['municipalities'] ?? [];
            $muni = collect($municipalities)->firstWhere('id', $placeEvent->municipalityId);
            $placeEvent->municipalityName = $muni != null ? $muni['name'] : "*No disponible";
            $colonies = $zipcodeData['colonies'] ?? [];
            $colony = collect($colonies)->firstWhere('id', $placeEvent->colonyId);
            $placeEvent->colonyName = $colony != null ? $colony['name'] : "*No disponible";
        } catch (\Throwable $th) {
            Log::error("Fail at get the zipcode data from the API at processing the LostDocument model: {message}", [
                "message" => $th->getMessage()
            ]);
        }
        $model->placeEvent = $placeEvent;

        // * retrive person data from API
        $person = $this->authApiService->getPersonById($data->people_id);
        
        // * check if has the permision for show the person
        if (array_key_exists(self::$MOSTRAR_PERSONA_PERMISSION, $permissions)) {
            $model->setPerson($person);
            $model->fullName = $person['fullName'] ?? "*No disponible";
        }
        else
        {
            $model->fullName = \App\Helpers\HideText::hide($person['fullName']);
        }

        return $model;
    }

}