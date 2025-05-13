<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
