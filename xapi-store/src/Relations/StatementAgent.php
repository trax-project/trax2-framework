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
     * Get the related agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(\Trax\XapiStore\Stores\Agents\Agent::class);
    }

    /**
     * Get the related statement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statement()
    {
        return $this->belongsTo(\Trax\XapiStore\Stores\Statements\Statement::class);
    }
}
