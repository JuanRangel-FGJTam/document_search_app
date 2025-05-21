<?php

namespace App\ViewModels;

class SearchResult
{
    public int $id;
    public string $plateNumber;
    public string $fullName;
    public string $registerDate;
    public array $person;
    public array $vehicle;
}