<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Laravel\Scout\Searchable;

class HechosCP extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'PGJ_HECHOS_CP';
    protected $primaryKey = 'ID_LUGAR_HECHOS_CP';

    protected $fillable = [
        "ID_LUGAR_HECHOS_CP",   // int <nullable>
        "ID_EXTRAVIO",          // int <nullable>
        "CPcodigo",             // varchar(8) <nullable>
        "CPmunicipio",          // varchar(50) <nullable>
        "CPcolonia",            // varchar(100) <nullable>
        "CPcalle",              // varchar(150) <nullable>
        "FECHA_REGISTRO"        // datetime <nullable>
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function extravio(): BelongsTo
    {
        return $this->belongsTo(Extravio::class,'ID_EXTRAVIO','ID_EXTRAVIO');
    }

}
