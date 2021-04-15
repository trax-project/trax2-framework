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
            'magicAgent',
        ];
    }

    /**
     * Filter: magic.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function magicAgentFilter($field, Query $query = null)
    {
        // We don't check null values. This may happen when the UI field is empty.
        // And it will return no result, which is what we want.
        return $this->getMagicAgentFilter($field, 'agent');
    }
}
