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
        $url = $request->url();
        if (!app()->environment(['local', 'testing'])) {
            // Verificar si es una IP o un dominio
            $host = $request->getHost();
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                // Si es una IP, usar http
                $url = preg_replace("/^https:/i", "http:", $url);
            } else {
                // Si es un dominio, usar https
                $url = preg_replace("/^http:/i", "https:", $url);
            }
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
