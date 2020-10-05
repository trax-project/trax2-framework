<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;

trait AgentFilters
{
    use MagicFilters;
    use XapiAgentFilters {
        dynamicFilters as xapiDynamicFilters;
    }

    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return array_merge($this->xapiDynamicFilters(), [
            'magic',
        ]);
    }

    /**
     * Filter: magic.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function magicFilter($field, Query $query = null)
    {
        return $this->getMagicAgentFilter($field);
    }
}
