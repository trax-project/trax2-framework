<?php

namespace Trax\XapiStore\Stores\Logs;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trax_xapi_logs';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
    ];
}
