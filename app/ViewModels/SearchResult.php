<?php

namespace App\ViewModels;
use App\Models\Vehicle;
use App\Models\Misplacement;

class SearchResult
{
    public string $documentNumber;
    public int $vehicleId;
    public string $plateNumber;
    public string $serialNumber;
    public string $personId;
    public string $fullName;
    public ?string $carDescription;
    public string $registerDate;
    public ?SearchResultPerson $person;
    public ?SearchResultVehicle $vehicle;
    public SearchResultMisplacement $misplacement;

    public function __construct(string $documentNumber, int $vehicleId)
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
        $this->misplacement = $_misplacement;
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

    public function __construct(Misplacement $misplacement)
    {
        $this->documentNumber = $misplacement->document_number;
        $this->statusId = $misplacement->lost_status_id;
        $this->statusName = $misplacement->lostStatus->name;
    }
}