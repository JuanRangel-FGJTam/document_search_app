<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // TODO: Apply some search

        // * return the view
        return Inertia::render('Search/Index', [
            'search' => $request->input('search'),
        ]);
    }

}
