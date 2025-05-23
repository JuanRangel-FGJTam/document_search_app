<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalUser extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;
    
    protected $fillable = [
        'cuip',
        'organization_id'
    ];

    protected $appends = [
        'permissions_overrided'
    ];

    public function organization() : BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function searchPermissions() : BelongsToMany
    {
        return $this->belongsToMany(SearchPermission::class, 'external_user_search_permission');
    }

    public function getPermissionsOverridedAttribute()
    {
        return $this->searchPermissions()->exists();
    }

    /**
     * currentPermissions
     *
     * @return array<int,string>
     */
    public function currentPermissions()
    {
        if ($this->getPermissionsOverridedAttribute()) {
            $permissions = $this->searchPermissions;
        } else {
            $permissions = $this->organization ? $this->organization->searchPermissions : [];
        }

        return $permissions->pluck('name', 'id')->toArray();
    }

}
