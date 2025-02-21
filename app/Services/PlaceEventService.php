<?php

namespace App\Services;

use App\Models\PlaceEvent;

class PlaceEventService
{
    public function getByMisplacementId(string $misplacement_id){
        return PlaceEvent::where('misplacement_id',$misplacement_id)->first();
    }
}
