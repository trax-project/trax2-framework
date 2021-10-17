<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;
use Trax\XapiStore\Stores\Agents\AgentFactory;

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
            'uiCombo',
            'uiObjectType',
            'uiName',
            'agents',
        ]);
    }

    /**
     * Filter: uiCombo.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiComboFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicAgentFilter($field);
    }

    /**
     * Filter: uiObjectType.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiObjectTypeFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return [
            ['is_group' => ($field == 'Group' ? 1 : 0)],
        ];
    }

    /**
     * Filter: uiName.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiNameFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return [
            ['name' => ['$text' => $field]],
        ];
    }

    /**
     * Filter: agents.
     *
     * @param  array  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function agentsFilter($field, Query $query = null)
    {
        $vids = array_map(function ($agent) {
            return AgentFactory::virtualId($agent);
        }, $field);

        return [
            ['vid' => ['$in' => $vids]]
        ];
    }
}
