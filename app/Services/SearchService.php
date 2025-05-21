<?php

namespace App\Services;

use App\ViewModels\SearchResult;

class SearchService
{
    /**
     * search
     *
     * @param mixed $search
     * @return array<SearchResult>
     */
    public function search(string $search)
    {
        $response = [];

        for ($i = 1; $i <= 3; $i++) {
            $model = new SearchResult();
            $model->id = $i;
            $model->plateNumber = $search . $i;
            $model->fullName = "Juan Salvador Rangel A.";
            $model->registerDate = now()->format("Y-m-d H:i:s");
            $model->vehicle = [
                'misplacement_id' => 'cbdfdc67-b3d8-49a9-b141-bb757f4abed2',
                'vehicle_brand_id' => 16,
                'vehicle_brand_name' => "Kia",
                'vehicle_sub_brand_id' => 6,
                'vehicle_sub_brand_name' => 'Forte',
                'vehicle_type_id' => 1,
                'vehicle_type_name' => "Sedan",
                'vehicle_model_id' => 2017,
                'plate_type_id' => 1,
                'plate_type_id' => "Frontal",
                'plate_number' => $search . $i,
                'serie_number' => '001920391039103123'
            ];
            $model->person = [
                "personId"=> "cbdfdc67-b3d8-49a9-b141-bb757f4abed2",
                "rfc"=> "RAAJ931217SX5",
                "curp"=> "RAAJ931217HGTNLN05",
                "name"=> "JUAN SALVADOR",
                "firstName"=> "RANGEL",
                "lastName"=> "ALMAGUER",
                "fullName"=> "JUAN SALVADOR RANGEL ALMAGUER",
                "email"=> "chava_r17@hotmail.com",
                "birthdate"=> "1993-12-17",
                "genderId"=> 1,
                "genderName"=> "MASCULINO",
                "maritalStatusId"=> 1,
                "maritalStatusName"=> "SOLTERO(A)",
                "nationalityId"=> 31,
                "nationalityName"=> "MEXICANA",
                "occupationId"=> 87,
                "occupationName"=> "INGENIERO",
                "birthdateFormated"=> "17/DIC/1993",
                "age"=> 31
            ];
            $response[] = $model;
        }
        
        return $response;
    }
}
