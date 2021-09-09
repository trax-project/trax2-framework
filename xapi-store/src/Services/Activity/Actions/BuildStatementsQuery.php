<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Trax\Repo\Querying\Query;

trait BuildStatementsQuery
{
    use FilterStatementsActivity, FilterStatementsMagicObject, FilterStatementsMagicContext;

    /**
     * Statement filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function buildStatementsQuery(Query $query): void
    {
        $this->filterStatementsActivity($query);
        $this->filterStatementsMagicObject($query);
        $this->filterStatementsMagicContext($query);
    }
}
