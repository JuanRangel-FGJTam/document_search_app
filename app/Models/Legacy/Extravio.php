<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;

class Extravio extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_EXTRAVIOS';
    protected $primaryKey = 'ID_EXTRAVIO';


    public function estadoExtravio(){
        return $this->belongsTo(EstadoExtravio::class,'ID_ESTADO_EXTRAVIO','ID_ESTADO_EXTRAVIO');
    }

    public function identificacion(){
        return $this->belongsTo(Identificacion::class, 'ID_IDENTIFICACION','ID_IDENTIFICACION');
    }

}
