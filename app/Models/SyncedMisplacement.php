<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncedMisplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'misplacement_id',
        'legacy_id',
        'failed',
        'message'
    ];
}
