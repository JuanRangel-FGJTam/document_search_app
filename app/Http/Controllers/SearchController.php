<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\SearchService;
use App\ViewModels\SearchResult;
use App\Models\VehicleType;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        // * validate and retrive the search parameters
        $request->validate([
            "search" => "required|string|min:3|max:120"
        ]);
        $input_search = $request->input('search');
        $serach_type = $request->input('type', 'plate_number');

        $months = [
            1 => "Ene",
            2 => "Feb",
            3 => "Mar",
            4 => "Abr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Ago",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dic"
        ];

        $vehicleTypes = VehicleType::orderBy('name')->select(['id', 'name'])->get()->all();

        // * return the view
        return Inertia::render('Search/Index', [
            'search' => $request->input('search'),
            'searchTypes' => \App\Helpers\SearchTypes::$types,
            "results" => Inertia::lazy( fn() => $this->searchData($input_search, $serach_type)),
            "searchType" => $serach_type,
            "months" => $months,
            "vehicleTypes" => $vehicleTypes
        ]);
    }

    public function searchFilters(Request $request)
    {
        // * validate and retrive the search parameters
        $request->validate([
            "credential" => "nullable|integer",
            "type" => "nullable|integer",
            "month" => "nullable|integer",
            "year" => "nullable|integer",
        ]);
        $hasCredential = $request->input('credential', 0);
        $vehicleType = $request->input('type', 0);
        $month = $request->input('month', now()->month);
        $yaer = $request->input('year', now()->year);


        $months = [
            1 => "Ene",
            2 => "Feb",
            3 => "Mar",
            4 => "Abr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Ago",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dic"
        ];

        $vehicleTypes = VehicleType::orderBy('name')->select(['id', 'name'])->get()->all();

        // * return the view
        return Inertia::render('Search/Index', [
            'search' => $request->input('search'),
            'searchTypes' => \App\Helpers\SearchTypes::$types,
            "results" => Inertia::lazy( fn() => $this->searchDataByFilters($hasCredential, $vehicleType, $month, $yaer)),
            "months" => $months,
            "vehicleTypes" => $vehicleTypes
        ]);
    }

    public function showResult($vehicleId)
    {
        if (str_starts_with($vehicleId, 'L')) {
            $idObjeto = ltrim($vehicleId, 'L');
            $searchResult = $this->searchService->finByVehicleId($idObjeto, SearchService::$SOURCE_LEGACY);
        }
        elseif(str_starts_with($vehicleId, 'D'))
        {
            $documentId = ltrim($vehicleId, 'D');
            $searchResult = $this->searchService->finByVehicleId($documentId, SearchService::$SOURCE_LOSDOCUMENT);
        }
        else
        {
            $searchResult = $this->searchService->finByVehicleId($vehicleId);
        }

        return Inertia::render("Search/Result", [
            "searchResult" => $searchResult
        ]);
    }

    /**
     * search_plates
     *
     * @param  string $searchString
     * @return array<SearchResult>
     */
    private function searchData($searchString, $searchType)
    {
        $array_search = array_map('trim', explode(',', $searchString));
        $results = [];
        foreach($array_search as $searchString)
        {
            $res = $this->searchService->search($searchString, $searchType);
            foreach ($res as $vehicle) {
                if (!in_array($vehicle, $results, false)) {
                    $results[] = $vehicle;
                }
            }
        }
        return $results;
    }

    /**
     * search_plates
     *
     * @param  string $searchString
     * @return array<SearchResult>
     */
    private function searchDataByFilters($hasCredential, $vehicleType, $month, $year)
    {
        return $this->searchService->getData($hasCredential, $vehicleType, $month, $year);
    }

}
