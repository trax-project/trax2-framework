<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentFactory;

trait FilterStatementsRelatedAgents
{
    /**
     * Agent filtering.
     *
     * @param  \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsRelatedAgents(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('relatedAgents')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // Get the agent ids.
        $vids = array_map(function ($xapiAgent) {
            return AgentFactory::virtualId($xapiAgent);
        }, $query->filter('relatedAgents'));

        $agentIds = $this->repository->whereVidIn($vids, $query)->pluck('id')->all();

        // Adapt the query.
        $callback = $this->filterStatementsRelatedAgentsCallback($agentIds);

        // Modify the filters.
        $query->removeFilter('relatedAgents');
        $query->addFilter(['id' => ['$in' => $callback]]);
    }

    /**
     * Get callback for related agents filter.
     *
     * @param  array  $agentIds
     * @return callable
     */
    protected function filterStatementsRelatedAgentsCallback(array $agentIds): callable
    {
        return function ($query) use ($agentIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->whereIn('agent_id', $agentIds);
        };
    }
}
