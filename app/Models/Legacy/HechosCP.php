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



}
