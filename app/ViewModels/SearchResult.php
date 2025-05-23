<?php

namespace App\ViewModels;
use App\Models\Vehicle;
use App\Models\Misplacement;
use App\Models\PlaceEvent;

class SearchResult
{
    public string $documentNumber;
    public string $vehicleId;
    public string $plateNumber;
    public string $serialNumber;
    public ?string $personId;
    public string $fullName;
    public ?string $carDescription;
    public string $registerDate;
    public ?SearchResultPerson $person;
    public ?SearchResultVehicle $vehicle;
    public SearchResultMisplacement $misplacement;
    public ?SearchResultPlaceEvent $placeEvent;

    public function __construct(string $documentNumber, string $vehicleId)
    {
        $this->documentNumber = $documentNumber;
        $this->vehicleId = $vehicleId;
    }

    public function setVehicle(Vehicle $vehicle)
    {
        $_vehicle = new SearchResultVehicle($vehicle);
        $this->vehicle = $_vehicle;
        $this->carDescription = implode(" ", [
            $_vehicle->typeName,
            $_vehicle->brandName,
            $_vehicle->subBrandName,
            $_vehicle->modelYear
        ]);
    }

    public function setPerson($person)
    {
        $_person = new SearchResultPerson($person);
        $this->person = $_person;
    }

    public function setMisplacement(Misplacement $misplacement)
    {
        $_misplacement = new SearchResultMisplacement($misplacement);
        $_misplacement->setData($misplacement);
        $this->misplacement = $_misplacement;
    }

    public function setLegacyMisplacement($data)
    {
        $_misplacement = new SearchResultMisplacement();
        $_misplacement->documentNumber = $data->ID_EXTRAVIO;
        $_misplacement->statusId = $data->ID_ESTADO_EXTRAVIO;
        $_misplacement->statusName = $data->ESTADO_EXTRAVIO;
        $this->misplacement = $_misplacement;
    }

    public function setPlaceEvent(PlaceEvent $placeEvent, array $zipcodeData)
    {
        $searchPlaceEvent = new SearchResultPlaceEvent();
        $searchPlaceEvent->setData($placeEvent, $zipcodeData);
        $this->placeEvent = $searchPlaceEvent;
    }

    public function setLegacyPlaceEvent($data)
    {
        $searchPlaceEvent = new SearchResultPlaceEvent();
        $searchPlaceEvent->lostDate = $data->FECHA_EXTRAVIO;
        $searchPlaceEvent->zipCode = "*No Capturado";
        $searchPlaceEvent->municipalityId = $data->ID_MUNICIPIO;
        $searchPlaceEvent->municipalityName = $data->MUNICIPIO;
        $searchPlaceEvent->colonyId = $data->ID_COLONIA;
        $searchPlaceEvent->colonyName = $data->COLONIA;
        $searchPlaceEvent->street = $data->CALLE;
        $searchPlaceEvent->description = $data->DESCRIPCION;
        $this->placeEvent = $searchPlaceEvent;
    }
}

class SearchResultVehicle
{
    public int $brandId;
    public string $brandName;
    public int $subBrandId;
    public string $subBrandName;
    public int $typeId;
    public string $typeName;
    public string $plateNumber;
    public string $serieNumber;
    public string $owner;
    public ?string $modelYear;

    public function __construct(Vehicle $vehicle)
    {
        $this->brandId = $vehicle->vehicle_brand_id;
        $this->brandName = $vehicle->vehicleBrand->name;
        $this->subBrandId = $vehicle->vehicle_sub_brand_id;
        $this->subBrandName = $vehicle->vehicleSubBrand->name;
        $this->typeId = $vehicle->vehicle_type_id;
        $this->typeName = $vehicle->vehicleType->name;
        $this->plateNumber = $vehicle->plate_number;
        $this->serieNumber = $vehicle->serie_number;
        $this->owner = $vehicle->owner;
        $this->modelYear = $vehicle->vehicleModel?->name;
    }
}

class SearchResultPerson
{
    public string $person_id;
    public string $name;
    public string $firstName;
    public string $lastName;
    public string $fullName;
    public string $curp;
    public string $email;
    public string $birthDate;
    public string $genderId;
    public string $genderName;
    public string $occupationId;
    public string $occupationName;
    public int $age;

    public function __construct($person)
    {
        $this->person_id = $person['personId'];
        $this->name = $person['name'];
        $this->firstName = $person['firstName'];
        $this->lastName = $person['lastName'];
        $this->fullName = $person['fullName'];
        $this->curp = $person['curp'];
        $this->email = $person['email'];
        $this->birthDate = $person['birthdateFormated'];
        $this->genderId = $person['genderId'];
        $this->genderName = $person['genderName'];
        $this->occupationId = $person['occupationId'];
        $this->occupationName = $person['occupationName'];
        $this->age = $person['age'];
    }
}

class SearchResultMisplacement
{
    public string $documentNumber;
    public string $statusId;
    public string $statusName;

    public function setData(Misplacement $misplacement)
    {
        $this->documentNumber = $misplacement->document_number;
        $this->statusId = $misplacement->lost_status_id;
        $this->statusName = $misplacement->lostStatus->name;
    }
}

class SearchResultPlaceEvent
{
    public string $lostDate;
    public string $zipCode;
    public string $municipalityId;
    public string $municipalityName;
    public string $colonyId;
    public string $colonyName;
    public string $street;
    public string $description;

    public function setData(PlaceEvent $placeEvent, array $zipcodeData)
    {
        $this->lostDate = $placeEvent->lost_date;
        $this->zipCode = $placeEvent->zipcode;

        $municipalities = $zipcodeData['municipalities'] ?? [];
        $muni = collect($municipalities)->firstWhere('id', $placeEvent->municipality_api_id);
        $this->municipalityId = $placeEvent->municipality_api_id;
        $this->municipalityName = $muni != null ? $muni['name'] : "*No disponible";

        $this->colonyId = $placeEvent->colony_api_id;

        $colonies = $zipcodeData['colonies'] ?? [];
        $colony = collect($colonies)->firstWhere('id', $placeEvent->colony_api_id);
        $this->colonyName = $colony != null ? $colony['name'] : "*No disponible";

        $this->street = $placeEvent->street;
        $this->description = $placeEvent->description;
    }

}