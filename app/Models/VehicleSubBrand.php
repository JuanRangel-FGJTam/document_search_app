<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleSubBrand extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'vehicle_brand_id',
    ];
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }
}
