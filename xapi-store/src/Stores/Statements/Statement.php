<?php

namespace Trax\XapiStore\Stores\Statements;

use Illuminate\Database\Eloquent\Model;

class Statement extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_statements';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
        'voided' => 'boolean',
        'pending' => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'voided' => false,
        'pending' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'data', 'voided', 'pending'];

    /**
     * Get the related agents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agents()
    {
        return $this->belongsToMany(
            \Trax\XapiStore\Stores\Agents\Agent::class,
            'trax_xapi_statement_agent',
            'statement_id',
            'agent_id'
        )->withPivot('type', 'sub', 'group');
    }

    /**
     * Get the related verbs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function verbs()
    {
        return $this->belongsToMany(
            \Trax\XapiStore\Stores\Verbs\Verb::class,
            'trax_xapi_statement_verb',
            'statement_id',
            'verb_id'
        )->withPivot('sub');
    }

    /**
     * Get the related activities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activities()
    {
        return $this->belongsToMany(
            \Trax\XapiStore\Stores\Activities\Activity::class,
            'trax_xapi_statement_activity',
            'statement_id',
            'activity_id'
        )->withPivot('type', 'sub');
    }

    /**
     * Get the agent relations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agentRelations()
    {
        return $this->hasMany(\Trax\XapiStore\Relations\StatementAgent::class);
    }

    /**
     * Get the verb relations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verbRelations()
    {
        return $this->hasMany(\Trax\XapiStore\Relations\StatementVerb::class);
    }

    /**
     * Get the activity relations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityRelations()
    {
        return $this->hasMany(\Trax\XapiStore\Relations\StatementActivity::class);
    }
}
