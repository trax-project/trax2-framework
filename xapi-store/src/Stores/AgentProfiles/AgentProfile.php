<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Illuminate\Database\Eloquent\Model;

class AgentProfile extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_agent_profiles';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'agent' => 'object',
        'data' => 'object',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id', 'activity_id', 'data'];
}
