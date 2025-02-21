<?php

namespace App\Services;

use App\Models\People;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Misplacement;

class AuthApiService
{
    static function baseUrl(): string
    {
        return config('app.AUTH_API_ENDPOINT');
    }

    static function authorizationValue()
    {
        return sprintf('Bearer %s', config('app.AUTH_API_TOKEN'));
    }

    public function getPersonById(string $person_id)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id));

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public static function getUserLastAddress(string $person_id)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/address/'));

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }


    public function getUserContactType(string $person_id, string $typeId)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/contactInformation?typeId=' . $typeId));

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }


    public function getZipCode(string $zipCode)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/zipcode/' . $zipCode));

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }



}
