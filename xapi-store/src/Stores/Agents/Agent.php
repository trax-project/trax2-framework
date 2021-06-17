<?php

namespace Trax\XapiStore\Stores\Agents;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_agents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'person_id', 'pseudonymized'];

    /**
     * Get the matching person.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(\Trax\XapiStore\Stores\Persons\Person::class);
    }

    /**
     * Get the pseudonymized agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pseudo()
    {
        return $this->belongsTo(\Trax\XapiStore\Stores\Agents\Agent::class, 'pseudo_id');
    }

    /**
     * Get the matching statements.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function statements()
    {
        return $this->belongsToMany(
            \Trax\XapiStore\Stores\Statements\Statement::class,
            'trax_xapi_statement_agent',
            'agent_id',
            'statement_id'
        )->withPivot('type', 'sub', 'group');
    }

    /**
     * Get the statement relations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statementRelations()
    {
        return $this->hasMany(\Trax\XapiStore\Relations\StatementAgent::class);
    }
}
