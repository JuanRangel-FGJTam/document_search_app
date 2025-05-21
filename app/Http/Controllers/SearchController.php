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
        Log::info('SearchController@search called via', ['method' => $method]);

        // * validate and retrive the search parameters
        $request->validate([
            "search" => "required|string|min:3|max:120"
        ]);
        $input_search = $request->input('search');

        Log::notice("Initialize searching", [
            "search" => $input_search
        ]);

        // * return the view
        return Inertia::render('Search/Index', [
            'search' => $request->input('search'),
            "results" => Inertia::lazy( fn() => $this->search_plates($input_search)),
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
            $results = array_merge($results, $res);
        }
        sleep(3);
        return $results;
    }

}
