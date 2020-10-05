<?php

namespace Trax\XapiStore\Stores\Attachments;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_attachments';

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
    protected $fillable = ['data'];
}
