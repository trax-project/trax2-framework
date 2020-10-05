<?php

namespace Trax\XapiStore\Stores\Persons;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_persons';

    /**
     * Get the matching agents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agents()
    {
        return $this->hasMany(\Trax\XapiStore\Stores\Agents\Agent::class);
    }
}
