<?php

namespace App\Contracts;

use App\ViewModels\SearchResult;

interface MisplacementRepositoryInterface
{
    /**
     * Search for misplacements by a keyword and type.
     *
     * @param string $search
     * @param array<string,mixed> $permissions
     * @param string|null $type
     * @return array<SearchResult>
     */
    public function search(string $search, array $permissions, ?string $type = null);

    /**
     * Get a misplacement by its vehicle, document or Object ID.
     *
     * @param string|int $vehicleId
     * @param array<string,mixed> $permissions
     * @return SearchResult
     */
    public function findByVehicleId(string|int $vehicleId, array $permissions);

}