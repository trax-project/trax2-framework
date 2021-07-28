<?php

namespace Trax\Auth\Stores\Users;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Date;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Contracts\ConsumerContract;
use Trax\Repo\ModelAttributes\ActivableModel;
use Trax\Repo\ModelAttributes\AdminModel;
use Trax\Repo\ModelAttributes\MetaModel;

class User extends Authenticatable implements HasPermissionsContract, ConsumerContract, MustVerifyEmail
{
    use ActivableModel, AdminModel, MetaModel, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'trax_users';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'source' => 'internal',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'email', 'password', 'firstname', 'lastname',
        'source', 'meta', 'role_id', 'entity_id', 'owner_id', 'password_changed_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['name', 'password_timestamp'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the name.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get the permissions.
     *
     * @return array
     */
    public function getPermissionsAttribute(): array
    {
        return $this->permissions();
    }

    /**
     * Create a new model instance given some data.
     *
     * @param  string  $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->password_changed_at = $this->freshTimestamp();
    }

    /**
     * Get the password timestamp.
     *
     * @return string
     */
    public function getPasswordTimestampAttribute(): string
    {
        return isset($this->password_changed_at)
            ? $this->password_changed_at
            : $this->created_at;
    }

    /**
     * Get the matching role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(\Trax\Auth\Stores\Roles\Role::class);
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
     * Is it a user?
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return true;
    }

    /**
     * Is it an app?
     *
     * @return bool
     */
    public function isApp(): bool
    {
        return false;
    }

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
     * Check if a consumer has a given permission.
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role_id ? $this->role->hasPermission($permission) : false;
    }

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function permissions(): array
    {
        return $this->role_id ? $this->role->permissions() : [];
    }

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function booleanPermissions(): array
    {
        return $this->role_id ? $this->role->booleanPermissions() : [];
    }
}
