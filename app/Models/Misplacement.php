<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['folio'] = $this->document_number;
        $array['people_name'] = $this->people->name ?? '';
        $array['people_email'] = $this->people->email ?? '';
        return $array;
    }

    /**
     * Get all of the synced data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syncedMisplacement(): HasOne
    {
        return $this->HasOne(SyncedMisplacement::class);
    }

}
