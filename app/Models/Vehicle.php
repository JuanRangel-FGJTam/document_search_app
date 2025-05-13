<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'misplacement_id',
        'vehicle_brand_id',
        'vehicle_sub_brand_id',
        'vehicle_type_id',
        'vehicle_model_id',
        'plate_type_id',
        'registration_doc_api_id',
        'plate_number',
        'serie_number',
        'owner'
    ];

    public function misplacement()
    {
        return $this->belongsTo(Misplacement::class);
    }
    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function vehicleSubBrand()
    {
        return $this->belongsTo(VehicleSubBrand::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function plateType()
    {
        return $this->belongsTo(PlateType::class);
    }


}
