<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Illuminate\Database\Eloquent\Model;

class ActivityProfile extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_activity_profiles';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id', 'activity_id', 'data'];
}
