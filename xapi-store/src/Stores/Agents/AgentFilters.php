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
            'xapiObjectType',
            'xapiName',
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
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicAgentFilter($field);
    }

    /**
     * Filter: xapiObjectType.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function xapiObjectTypeFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }

        // Group.
        if ($field == 'Group') {
            return [
                ['data->objectType' => 'Group'],
            ];
        }
        
        // Agent.
        return ['$or' => [
            ['data->objectType' => ['$exists' => false]],
            ['data->objectType' => 'Agent'],
        ]];
    }

    /**
     * Filter: xapiName.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function xapiNameFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return [
            ['data->name' => ['$text' => $field]],
        ];
    }
}
