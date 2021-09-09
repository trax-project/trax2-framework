<?php

namespace Trax\XapiStore\Services\StatementRequest\Actions;

use Trax\Repo\Querying\Query;

trait BuildStatementsQuery
{
    /**
     * Statement filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param bool  $reveal
     * @return void
     */
    public function buildStatementsQuery(Query $query, bool $reveal = true): void
    {
        if ($reveal) {
            app(\Trax\XapiStore\Services\Agent\AgentService::class)->buildStatementsQuery($query);
            // We can't apply relational filters when we can't reveal identities.
        }
        app(\Trax\XapiStore\Services\Verb\VerbService::class)->buildStatementsQuery($query);
        app(\Trax\XapiStore\Services\Activity\ActivityService::class)->buildStatementsQuery($query);
    }
}
