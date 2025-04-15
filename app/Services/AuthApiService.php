<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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

        if (!$response->successful()) {
            Log::warning("Error al obtener los datos de la persona:{type}:{message} ", ['type' => $person_id, 'message' => $response->getBody()]);
            return [];
        }

        return $response->json();
    }

    public static function getUserLastAddress(string $person_id)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/address/'));

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

    /**
     * get the colonies by zipCode
     * @throws \Exception throws a exception if the HttpRequest fails
     * @return mixed
     */
    public function getZipCode(string $zipCode)
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/zipcode/' . $zipCode));

        if (!$response->successful())
        {
            return [];
        }
        return $response->json();
    }


    public function storeProcesure(string $person_id, array $data)
    {
        $response = Http::withHeaders(['Accept' => 'application/json', 'Authorization' => self::authorizationValue()])
            ->post(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/procedures'), $data);

        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getProcedure(string $person_id, string $procedure_id)
    {
        $response = Http::withHeaders(['Accept' => 'application/json', 'Authorization' => self::authorizationValue()])
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/procedures/' . $procedure_id));

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

    public function getMunicipalities(int $stateId = 28): array
    {
        $response = Http::withHeaders([
            'Authorization' => self::authorizationValue()
        ])->get(Str::finish(self::baseUrl(), '/api/catalog/municipalities?stateId=' . $stateId));

        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getMunicipalityById(int $municipalityId): array
    {
        $response = Http::withHeaders([
            'Authorization' => self::authorizationValue()
        ])->get(Str::finish(self::baseUrl(), '/api/catalog/municipalities/' . $municipalityId));

        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getColonyById(int $colonyId): array
    {
        $response = Http::withHeaders([
            'Authorization' => self::authorizationValue()
        ])->get(Str::finish(self::baseUrl(), '/api/catalog/colonies/' . $colonyId));

        if (!$response->successful()) {
            return [];
        }
        return $response->json();
    }

    public function getIdentificationByType(string $person_id, string $documentTypeId): array
    {
        $response = Http::withHeader('Authorization', self::authorizationValue())
            ->get(Str::finish(self::baseUrl(), '/api/people/' . $person_id . '/document/' . $documentTypeId));

        if (!$response->successful()) {
            return [];
        }

        return $response->json();
    }
}
