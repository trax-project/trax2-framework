<?php

namespace Trax\Auth\Stores\Owners;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_owners';

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
    protected $fillable = ['name', 'meta'];

    /**
     * Get the associated entities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entities()
    {
        return $this->hasMany(\Trax\Auth\Stores\Entities\Entity::class);
    }
}
