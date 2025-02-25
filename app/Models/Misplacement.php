<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Misplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'lost_status_id',
        'people_id',
        'code',
        'hash_code',
        'document_number',
        'document_api_id',
        'validation_date',
        'observations',
        'registration_date',
        'cancellation_date',
        'cancellation_reason_description',
        'cancellation_reason_id',
    ];


    public function people()
    {
        return $this->belongsTo(People::class);
    }

    public function lostStatus()
    {
        return $this->belongsTo(LostStatus::class);
    }

    public function cancellationReason()
    {
        return $this->belongsTo(CancellationReason::class);
    }

    public function misplacementIdentifications()
    {
        return $this->hasOne(MisplacementIdentification::class);
    }


}
