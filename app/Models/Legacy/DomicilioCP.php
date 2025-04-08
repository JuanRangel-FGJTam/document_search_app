<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;

class DomicilioCP extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_DOMICILIO_CP';
    protected $primaryKey = 'ID_DOMICILIO';
    public $timestamps = false;

    public function extravio(){
        return $this->belongsTo(Extravio::class,'ID_EXTRAVIO','ID_EXTRAVIO');
    }

}
