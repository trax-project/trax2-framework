<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentRepository;

trait RequestMagicActor
{
    /**
     * Agent filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicActor(Query $query = null, $ownerId = null, bool $reveal = true): bool
    {
        // We can't make a relational request.
        if (!$reveal
            || !$query->hasFilter('magicActor')
            || !config('trax-xapi-store.tables.agents', false)
            || !config('trax-xapi-store.relations.statements_agents', false)
        ) {
            return true;
        }

        // Only some magic filters support relational requests.
        $magicActor = $query->filter('magicActor');
        if (!$this->relationalMagicAgent($magicActor)) {
            return true;
        }

        // Get the matching agents.
        $agents = resolve(AgentRepository::class)->addFilter([
            'magic' => $magicActor,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        // Modify the filters.
        $callback = $this->magicActorCallback($agents);
        $query->removeFilter('magicActor');
        $query->addFilter(['agentRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @return callable
     */
    protected function magicActorCallback(Collection $agents): callable
    {
        return function ($query) use ($agents) {
            return $query
                ->whereIn('agent_id', $agents->pluck('id'))
                ->where('type', 'actor')
                ->where('sub', false);
        };
    }
}
