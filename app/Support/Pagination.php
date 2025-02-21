<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
class Pagination {
    public static function paginate($items, Request $request, $perPage = 50) : LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $url = $request->url();
        if (config('app.env') === 'production' && !str_starts_with($url, 'https://')) {
            $url = preg_replace("/^http:/i", "https:", $url);
        }

        return new LengthAwarePaginator(
            $currentPageItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $url, 'query' => $request->query()]
        );
    }
}
