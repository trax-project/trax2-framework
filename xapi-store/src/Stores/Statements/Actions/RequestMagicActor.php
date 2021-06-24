<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentService;
use Trax\XapiStore\Relations\StatementAgent;

trait RequestMagicActor
{
    /**
     * Agent filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicActor(Query $query, bool $reveal = true): bool
    {
        // We can't make a relational request.
        if (!$reveal
            || !$query->hasFilter('uiActor')
            || !config('trax-xapi-store.tables.agents', false)
            || !config('trax-xapi-store.relations.statements_agents', false)
        ) {
            return true;
        }

        // Only some UI filters support relational requests.
        $uiActor = $query->filter('uiActor');
        if (!$this->relationalMagicAgent($uiActor)) {
            return true;
        }

        // Get the matching agents.
        $agents = app(AgentService::class)->whereUiCombo($uiActor, $query);

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        $agentIds = $agents->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiActor');
        $query->addFilter(['id' => ['$in' => $this->magicActorCallback($agentIds)]]);

        return true;
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Illuminate\Support\Collection  $agentIds
     * @return callable
     */
    protected function magicActorCallback(Collection $agentIds): callable
    {
        return function ($query) use ($agentIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->whereIn('agent_id', $agentIds)
                ->where('type', StatementAgent::TYPE_ACTOR)
                ->where('sub', false);
        };
    }
}
