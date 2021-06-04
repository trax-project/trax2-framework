<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\Agent;
use Trax\XapiStore\Stores\Agents\AgentFactory;
use Trax\XapiStore\Stores\Agents\AgentService;

trait RequestAgent
{
    /**
     * Agent filtering.
     *
     * @param  \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @return bool
     */
    protected function requestAgent(Query $query = null, $ownerId = null): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('agent')
            || !config('trax-xapi-store.tables.agents', false)
            || !config('trax-xapi-store.relations.statements_agents', false)
        ) {
            return true;
        }

        // If we don't want to reveal the result.
        // In this case, we make a classic JSON request.
        // We don't use the relational capabilities because they are based on clear agents.
        if ($this->dontRevealAgents($query)) {
            return true;
        }

        // Get the agent.
        $agents = resolve(AgentService::class);
        if (!$agent = $agents->addFilter([
            'vid' => AgentFactory::virtualId($query->filter('agent')),
            'owner_id' => $ownerId
        ])->get()->first()) {
            // No matching.
            return false;
        }

        // Adapt the query.
        $callback = $query->hasOption('related_agents') && $query->option('related_agents') == 'true'
            ? $this->relatedAgentsCallback($agent)
            : $this->agentCallback($agent);

        // Modify the filters.
        $query->removeFilter('agent');
        $query->addFilter(['agentRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Check if we must reveal agents or not.
     * We consider that they should be revealed by default,
     * except if the request is based on a pseudonymized agent.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function dontRevealAgents(Query $query = null): bool
    {
        if ($query->hasFilter('agent')) {
            $agent = json_decode($query->filter('agent'));
            if (isset($agent->account) && $agent->account->homePage == config('trax-xapi-store.gdpr.pseudo_iri')) {
                // Pseudonymized agent filter, we don't reveal.
                return true;
            }
        }
        return false;
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Trax\XapiStore\Stores\Agents\Agent  $agent
     * @return callable
     */
    protected function agentCallback(Agent $agent): callable
    {
        return function ($query) use ($agent) {
            return $query
                ->where('agent_id', $agent->id)
                ->whereIn('type', ['actor', 'object'])
                ->where('sub', false);
        };
    }

    /**
     * Get callback for related agents filter.
     *
     * @param  \Trax\XapiStore\Stores\Agents\Agent  $agent
     * @return callable
     */
    protected function relatedAgentsCallback(Agent $agent): callable
    {
        return function ($query) use ($agent) {
            return $query
                ->where('agent_id', $agent->id);
        };
    }
}
