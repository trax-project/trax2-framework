<?php

namespace Trax\Auth\Stores\Clients;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Contracts\StorePermissionsContract;
use Trax\Repo\ModelAttributes\ActivableModel;
use Trax\Auth\Traits\ModelWithPermissions;
use Trax\Repo\ModelAttributes\AdminModel;

class Client extends Model implements StorePermissionsContract
{
    use ActivableModel, ModelWithPermissions, AdminModel;

    /**
     * The table associated with the model.
     */
    protected $table = 'trax_clients';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'permissions' => '[]',
        'active' => true,
        'admin' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'meta', 'entity_id', 'owner_id'];
    
    /**
     * Get the number of associated accesses.
     *
     * @return int
     */
    public function getAccessesCountAttribute(): int
    {
        return $this->accesses->where('visible', true)->count();
    }

    /**
     * Get the matching entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(\Trax\Auth\Stores\Entities\Entity::class);
    }

    /**
     * Get the matching owner entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(\Trax\Auth\Stores\Owners\Owner::class);
    }

    /**
     * Get the accesses associated with this client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accesses()
    {
        return $this->hasMany(\Trax\Auth\Stores\Accesses\Access::class)->where('visible', true);
    }

    /**
     * Get the type of consumer. Some permissions are reserved to some types of consumers.
     *
     * @return string
     */
    public function consumerType(): string
    {
        return 'app';
    }
}
