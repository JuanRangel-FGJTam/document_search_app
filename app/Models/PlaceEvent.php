<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'misplacement_id',
        'lost_date',
        'zipcode',
        'municipality_api_id',
        'colony_api_id',
        'street',
        'description',
    ];


    public function misplacement()
    {
        return $this->belongsTo(Misplacement::class);
    }

}
