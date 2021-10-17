<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentFactory;
use Trax\XapiStore\Relations\StatementAgent;

trait FilterStatementsAgent
{
    /**
     * Agent filtering.
     *
     * @param  \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsAgent(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('agent')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // Get the agent.
        $vid = AgentFactory::virtualId($query->filter('agent'));
        if (!$agentId = $this->repository->idByVid($vid, $query)) {
            throw new NotFoundHttpException();
        }

        // Adapt the query.
        $callback = $query->hasOption('related_agents') && $query->option('related_agents') == 'true'
            ? $this->filterStatementsRelatedAgentCallback($agentId)
            : $this->filterStatementsAgentCallback($agentId);

        // Modify the filters.
        $query->removeFilter('agent');
        $query->addFilter(['id' => ['$in' => $callback]]);
    }

    /**
     * Get callback for agent filter.
     *
     * @param  int  $agentId
     * @return callable
     */
    protected function filterStatementsAgentCallback(int $agentId): callable
    {
        return function ($query) use ($agentId) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->where('agent_id', $agentId)
                ->whereIn('type', [StatementAgent::TYPE_ACTOR, StatementAgent::TYPE_OBJECT])
                ->where('sub', false);
        };
    }

    /**
     * Get callback for related agents filter.
     *
     * @param  int  $agentId
     * @return callable
     */
    protected function filterStatementsRelatedAgentCallback(int $agentId): callable
    {
        return function ($query) use ($agentId) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->where('agent_id', $agentId);
        };
    }
}
