<?php

namespace Trax\XapiStore\Relations;

use Illuminate\Database\Eloquent\Model;

class StatementActivity extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_statement_activity';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
