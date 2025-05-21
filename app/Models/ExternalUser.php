<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExternalUser extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;
    
    protected $fillable = [
        'cuip',
        'organization_id'
    ];

    public function searchPermissions() : BelongsToMany
    {
        return $this->belongsToMany(SearchPermission::class, 'external_user_search_permission');
    }
}
