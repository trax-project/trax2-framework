<?php

namespace Trax\XapiStore\Stores\States;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_states';

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
    protected $fillable = ['state_id', 'activity_id', 'agent', 'registration', 'data'];
}
