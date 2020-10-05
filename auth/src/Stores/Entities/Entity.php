<?php

namespace Trax\Auth\Stores\Entities;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_entities';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'meta', 'owner_id'];

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
     * Get the users associated with this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        $userClass = config('auth.providers.users.model');
        return $this->hasMany($userClass);
    }

    /**
     * Get the clients associated with this entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients()
    {
        return $this->hasMany(\Trax\Auth\Stores\Clients\Client::class);
    }
}
