<?php

namespace Trax\XapiStore\Services\Verb\Actions;

use Trax\Repo\Querying\Query;

trait BuildStatementsQuery
{
    use FilterStatementsVerb, FilterStatementsMagicVerb;

    /**
     * Statement filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function buildStatementsQuery(Query $query): void
    {
        $this->filterStatementsVerb($query);
        $this->filterStatementsMagicVerb($query);
    }
}
