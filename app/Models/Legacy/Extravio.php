<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;

class Extravio extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_EXTRAVIOS';
    protected $primaryKey = 'ID_EXTRAVIO';

    protected $fillable = [
        "ID_EXTRAVIO",
        "ID_ESTADO_EXTRAVIO",
        "ID_IDENTIFICACION",
        "CODIGO",
        "CODIGO_CORTO",
        "CODIGO_COMPLETO",
        "IDENTIFICACION",
        "IDENTIFICACION_DESC",
        "ID_TIPO_DOCUMENTO",
        "NUMERO_DOCUMENTO",
        "NOMBRE",
        "PATERNO",
        "MATERNO",
        "ESPECIFIQUE",
        "DESCRIPCION",
        "FECHA_EXTRAVIO",
        "FECHA_REGISTRO",
        "ACTIVO",
        "OBSERVACIONES_CANCELACION",
        "ID_MOTIVO_CANCELACION",
        "FECHA_CANCELACION",
        "idUsuario",
        "ExtravioPlaca",
    ];

    public function estadoExtravio(){
        return $this->belongsTo(EstadoExtravio::class,'ID_ESTADO_EXTRAVIO','ID_ESTADO_EXTRAVIO');
    }

    public function identificacion(){
        return $this->belongsTo(Identificacion::class, 'ID_IDENTIFICACION','ID_IDENTIFICACION');
    }

    public function tipoDocumento(){
        return $this->belongsTo(TipoDocumento::class, 'ID_TIPO_DOCUMENTO','ID_TIPO_DOCUMENTO');
    }

    public function motivoCancelacion(){
        return $this->belongsTo(MotivoCancelacion::class, 'ID_MOTIVO_CANCELACION','idMotivo');
    }

    public function usuario(){
        return $this->belongsTo(UsuarioApi::class, 'idUsuario','idUsuario');
    }

    public function hechos(){
        return $this->belongsTo(Hechos::class, 'ID_EXTRAVIO','ID_EXTRAVIO');
    }

    public function hechosCP(){
        return $this->belongsTo(HechosCP::class, 'ID_EXTRAVIO','ID_EXTRAVIO');
    }

}
