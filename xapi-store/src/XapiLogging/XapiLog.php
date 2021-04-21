<?php

namespace Trax\XapiStore\XapiLogging;

use Illuminate\Database\Eloquent\Model;

class XapiLog extends Model
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
