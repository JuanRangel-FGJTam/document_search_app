<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchPermission extends Model
{
    protected $table = 'search_permissions';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'name',
        'description'
    ];
}