<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\AgentFilter;
use Trax\XapiStore\Stores\Agents\AgentFactory;

trait XapiAgentFilters
{
    use AgentFilter;

    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'agent',
        ];
    }

    /**
     * Filter: agent.
     *
     * @param  string|array|object  $agent
     * @param  \Trax\Repo\Querying\Query|null  $query
     * @return array
     */
    public function agentFilter($agent, Query $query = null)
    {
        return [
            ['vid' => AgentFactory::virtualId($agent)]
        ];
    }
}
