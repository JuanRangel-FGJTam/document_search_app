<?php

namespace App\Services;

use App\Models\People;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Misplacement;

class MisplacementService
{
    public function getAllByStatusId(string $lost_status)
    {
        return Misplacement::where('lost_status_id', $lost_status)->get();
    }

    public function getById(string $misplacement_id){
        return Misplacement::find($misplacement_id);
    }



    public function searchByFolioAndCode(string $document_number, string $code)
    {
        return Misplacement::where('document_number', $document_number)
            ->where('code', $code)
            ->first();
    }

    public function searchByHashCode(string $hashCode)
    {
        return Misplacement::where('hash_code', $hashCode)
            ->first();
    }
}
