<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleSubBrand extends Model
{
    use HasFactory;
    protected $fillable = [
        'vehicle_brand_id',
        'vehicle_type_id',
        'name',
    ];

    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
