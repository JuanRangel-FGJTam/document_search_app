<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    use HasFactory;
    protected $table = 'vehicle_brands';
    protected $fillable = [
        'name',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_brand_id');
    }

    public function subBrands()
    {
        return $this->hasMany(VehicleSubBrand::class, 'vehicle_brand_id');
    }
}
