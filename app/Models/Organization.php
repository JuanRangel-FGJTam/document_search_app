<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    protected $fillable = [
        'id',
        'name',
        'active'
    ];

    public function searchPermissions() : BelongsToMany
    {
        return $this->belongsToMany(SearchPermission::class, 'organization_search_permission');
    }

}