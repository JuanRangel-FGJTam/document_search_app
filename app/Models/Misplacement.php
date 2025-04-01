<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
class Misplacement extends Model
{
    use HasFactory;
    use Searchable;
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
        'canceled_by'
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

    public function user(){
        return $this->belongsTo(User::class, 'canceled_by');
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['folio'] = $this->document_number;
        $array['people_name'] = $this->people->name ?? '';
        $array['people_email'] = $this->people->email ?? '';
        return $array;
    }

}
