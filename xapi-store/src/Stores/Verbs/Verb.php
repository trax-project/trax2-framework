<?php

namespace Trax\XapiStore\Stores\Verbs;

use Illuminate\Database\Eloquent\Model;

class Verb extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_verbs';

    /**
     * Get the matching statements.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function statements()
    {
        return $this->belongsToMany(
            \Trax\XapiStore\Stores\Statements\Statement::class,
            'trax_xapi_statement_verb',
            'verb_id',
            'statement_id'
        )->withPivot('sub');
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
