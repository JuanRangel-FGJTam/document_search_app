<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MisplacementIdentification extends Model
{
    use HasFactory;

    protected $table = 'misplacement_ident_plate';

    protected $fillable = [
        'misplacement_id',
        'identification_type_id',
        'identification_number',
        'identification_file',
    ];

    public function identificationType()
    {
        return $this->belongsTo(IdentificationType::class, 'identification_type_id');
    }

}
