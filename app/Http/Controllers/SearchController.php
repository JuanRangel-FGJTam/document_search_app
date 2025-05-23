<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\SearchService;
use App\ViewModels\SearchResult;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        $method = $request->method();

        // * validate and retrive the search parameters
        $request->validate([
            "search" => "required|string|min:3|max:120"
        ]);
        $input_search = $request->input('search');

        // * return the view
        return Inertia::render('Search/Index', [
            'search' => $request->input('search'),
            "results" => Inertia::lazy( fn() => $this->search_plates($input_search)),
        ]);
    }

    public function showResult($vehicleId)
    {
        $searchResult = $this->searchService->finByVehicleId($vehicleId);

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
    private function search_plates($searchString)
    {
        $array_search = array_map('trim', explode(',', $searchString));
        $results = [];
        foreach($array_search as $searchString)
        {
            $res = $this->searchService->search($searchString);
            foreach ($res as $vehicle) {
                if (!in_array($vehicle, $results, false)) {
                    $results[] = $vehicle;
                }
            }
        }
        return $results;
    }

}
