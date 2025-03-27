<?php

namespace App\Services;

use App\Models\People;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Misplacement;
use Illuminate\Support\Facades\Log;

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
            Log::warning("Error al obtener los datos de la persona:{type}:{message} ",['type'=>$person_id, 'message'=>$response->getBody()]);
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


    public function storeProcesure(string $person_id, array $data)
    {
        $response = Http::withHeaders(['Accept' => 'application/json', 'Authorization' => self::authorizationValue()])
            ->post(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/procedures'), $data);

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getProcedure(string $person_id, string $procedure_id)
    {
        $response = Http::withHeaders(['Accept' => 'application/json', 'Authorization' => self::authorizationValue()])
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/procedures/'.$procedure_id));

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getDocumentById(string $person_id, string $documentTypeId)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/document/' . $documentTypeId));

        // * Validate response
        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }
}
