<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Pagination
{
    public static function paginate($items, Request $request, $perPage = 50): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Detectar correctamente el esquema HTTP/HTTPS
        $scheme = $request->getScheme(); // Detecta si es http o https
        $url = $scheme . '://' . $request->getHttpHost() . $request->getPathInfo();

        return new LengthAwarePaginator(
            $currentPageItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $url, 'query' => $request->query()]
        );
    }
}
