<?php

namespace App\Services;

use App\Models\LostDocument;
use App\Models\Misplacement;

class LostDocumentService
{

    public function getByMisplacementId(string $misplacement_id){
        return LostDocument::where('misplacement_id',$misplacement_id)->get();
    }
}
