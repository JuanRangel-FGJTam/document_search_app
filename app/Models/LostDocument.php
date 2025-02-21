<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'misplacement_id',
        'document_type_id',
        'document_number',
        'document_owner',
        'specification',
        'registration_date',
        'active',
    ];


    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function misplacement()
    {
        return $this->belongsTo(Misplacement::class);
    }

}
