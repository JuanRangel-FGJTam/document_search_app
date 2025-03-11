<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'misplacement_id',
        'rating_1',
        'rating_2',
        'rating_3',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
        'comments',
        'register_date'
    ];

    public function misplacement()
    {
        return $this->belongsTo(Misplacement::class);
    }

}
