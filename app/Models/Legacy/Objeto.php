<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;

class Objeto extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_OBJETOS';
    protected $primaryKey = 'ID_OBJETO';

    protected $fillable = [
        "ID_EXTRAVIO",
        "ID_TIPO_DOCUMENTO",
        "NUMERO_DOCUMENTO",
        "TITULAR_DOCUMENTO",
        "ESPECIFIQUE",
        "DESCRIPCION",
        "FECHA_REGISTRO",
        "ACTIVO",
        "IDMARCA",
        "IDTIPO",
        "IDMODELO",
        "NUMEROSERIE",
    ];

    public function tipoDocumento(){
        return $this->belongsTo(TipoDocumento::class,'ID_TIPO_DOCUMENTO','ID_TIPO_DOCUMENTO');
    }

}
