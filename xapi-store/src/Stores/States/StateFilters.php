<?php

namespace Trax\XapiStore\Stores\States;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;
use Trax\XapiStore\Traits\XapiDocumentFilters;

trait StateFilters
{
    use XapiDocumentFilters, MagicFilters;
    
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            // xAPI standard filters.
            'stateId', 'activityId', 'agent', 'since',

            // Additional filters.
            'uiAgent', 'uiActivity', 'uiState',
        ];
    }

    /**
     * Filter: uiAgent.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiAgentFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicAgentFilter($field);
    }

    /**
     * Filter: uiActivity.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiActivityFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicIriFilter($field, 'activity_id');
    }

    /**
     * Filter: uiState.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiStateFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return [
            ['state_id' => ['$text' => $field]],
        ];
    }
}
