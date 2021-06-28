<?php

namespace Trax\XapiStore\Relations;

use Illuminate\Database\Eloquent\Model;

class StatementAgent extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_statement_agent';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Types of relation.
     */
    const TYPE_ACTOR = 1;
    const TYPE_OBJECT = 2;
    const TYPE_INSTRUCTOR = 3;
    const TYPE_TEAM = 4;
    const TYPE_AUTHORITY = 5;

    /**
     * Get the related agent.
     * This needed for the pseudonimization process.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(\Trax\XapiStore\Stores\Agents\Agent::class);
    }
}
