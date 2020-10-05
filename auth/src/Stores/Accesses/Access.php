<?php

namespace Trax\Auth\Stores\Accesses;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Contracts\ConsumerContract;
use Trax\Auth\Contracts\StorePermissionsContract;
use Trax\Auth\Traits\ModelWithPermissions;
use Trax\Repo\ModelAttributes\ActivableModel;
use Trax\Repo\ModelAttributes\AdminModel;

class Access extends Model implements ConsumerContract, StorePermissionsContract
{
    use ActivableModel {
        isActive as isAccessActive;
    }
    use AdminModel {
        isAdmin as isStaticAdmin;
    }
    use ModelWithPermissions {
        hasPermission as hasStaticPermission;
        permissions as staticPermissions;
        booleanPermissions as staticBooleanPermissions;
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trax_accesses';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'inherited_permissions' => 'boolean',
        'meta' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'cors' => '',
        'inherited_permissions' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['client_id', 'credentials_id', 'credentials_type',
        'name', 'meta', 'inherited_permissions', 'cors'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['type'];


    /**
     * Get access type.
     *
     * @return string
     */
    public function getTypeAttribute($value): string
    {
        return $this->type();
    }

    /**
     * Get owner ID.
     *
     * @return int
     */
    public function getOwnerIdAttribute($value)
    {
        return $this->client->owner_id;
    }

    /**
     * Get entity ID.
     *
     * @return int
     */
    public function getEntityIdAttribute($value)
    {
        return $this->client->entity_id;
    }

    /**
     * Get the access type.
     *
     * @return string
     */
    public function type(): string
    {
        return \Str::of($this->credentials_type)->basename()->snake();
    }

    /**
     * Get the matching client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(\Trax\Auth\Stores\Clients\Client::class);
    }

    /**
     * Get the owning credentials model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function credentials()
    {
        return $this->morphTo();
    }

    /**
     * Is it a user?
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return false;
    }

    /**
     * Is it an app?
     *
     * @return bool
     */
    public function isApp(): bool
    {
        return true;
    }

    /**
     * Get the access activation.
     *
     * @return int
     */
    public function isActive(): int
    {
        return $this->isAccessActive() && $this->client->isActive();
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

    /**
     * Check if a consumer has a given permission.
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->inherited_permissions) {
            return $this->client->hasPermission($permission);
        }
        return $this->hasStaticPermission($permission);
    }

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function permissions(): array
    {
        if ($this->inherited_permissions) {
            return $this->client->permissions();
        }
        return $this->staticPermissions();
    }

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function booleanPermissions(): array
    {
        if ($this->inherited_permissions) {
            return $this->client->booleanPermissions();
        }
        return $this->staticBooleanPermissions();
    }

    /**
     * Check if a consumer is an admin and have all permissions.
     *
     * @return int
     */
    public function isAdmin(): int
    {
        if ($this->inherited_permissions) {
            return $this->client->isAdmin();
        }
        return $this->isStaticAdmin();
    }
}
