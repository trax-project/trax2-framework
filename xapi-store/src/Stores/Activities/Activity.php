<?php

namespace Trax\XapiStore\Stores\Activities;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'trax_xapi_activities';

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

    /**
     * Get the matching statements.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function statements()
    {
        return $this->belongsToMany(
            \Trax\XapiStore\Stores\Statements\Statement::class,
            'trax_xapi_statement_activity',
            'activity_id',
            'statement_id'
        )->withPivot('type', 'sub');
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
