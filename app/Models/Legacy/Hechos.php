<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;


class Hechos extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_HECHOS';
    protected $primaryKey = 'ID_LUGAR_HECHOS';


    protected $fillable = [
        "ID_LUGAR_HECHOS",  // int <nullable>
        "ID_EXTRAVIO",      // int <nullable>
        "ID_MUNICIPIO",     // int <nullable>
        "ID_LOCALIDAD",     // int <nullable>
        "ID_COLONIA",       // int <nullable>
        "ID_CALLE",         // int <nullable>
        "DESCRIPCION",      // varchar(max) <nullable>
        "FECHA",            // date <nullable>
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function extravio(): BelongsTo
    {
        return $this->belongsTo(Extravio::class,'ID_EXTRAVIO','ID_EXTRAVIO');
    }

}
