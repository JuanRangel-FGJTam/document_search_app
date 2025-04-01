<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;
use App\Models\Legacy\CatIdentificacion;

class Identificacion extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_IDENTIFICACION';
    protected $primaryKey = 'ID_IDENTIFICACION';


    public function cat_identificacion(){
        return $this->belongsTo(CatIdentificacion::class,  'ID_TIPO_IDENTIFICACION','ID_IDENTIFICACION');
    }

    public function cat_municipio(){
        return $this->belongsTo(CatMunicipio::class,  'ID_MUNICIPIO','ID_MUNICIPIO');
    }

    public function cat_localidad(){
        return $this->belongsTo(CatLocalidad::class,  'ID_LOCALIDAD','ID_LCLDD');
    }



}
