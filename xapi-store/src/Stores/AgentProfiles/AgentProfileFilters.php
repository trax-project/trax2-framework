<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;
use Trax\XapiStore\Traits\XapiDocumentFilters;

trait AgentProfileFilters
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
            'profileId', 'agent', 'since',

            // Additional filters.
            'uiAgent', 'uiProfile',
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
     * Filter: uiProfile.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiProfileFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return [
            ['profile_id' => ['$text' => $field]],
        ];
    }
}
