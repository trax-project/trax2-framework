<?php

namespace Trax\Auth\Stores\Roles;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Contracts\StorePermissionsContract;
use Trax\Auth\Traits\ModelWithPermissions;

class Role extends Model implements StorePermissionsContract
{
    use ModelWithPermissions;

    /**
     * The table associated with the model.
     */
    protected $table = 'trax_roles';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'description' => '',
        'meta' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'meta', 'owner_id'];

    /**
     * Get the type of consumer. Some permissions are reserved to some types of consumers.
     *
     * @return string
     */
    public function consumerType(): string
    {
        return 'user';
    }

    /**
     * Check if a consumer is an admin and have all permissions.
     *
     * @return int
     */
    public function isAdmin(): int
    {
        return false;
    }

    /**
     * Get the owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(\Trax\Auth\Stores\Owners\Owner::class);
    }

    /**
     * Get the users with this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        $userClass = config('auth.providers.users.model');
        return $this->hasMany($userClass);
    }
}
