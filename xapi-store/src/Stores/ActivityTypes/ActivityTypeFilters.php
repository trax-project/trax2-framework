<?php

namespace Trax\XapiStore\Stores\ActivityTypes;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;

trait ActivityTypeFilters
{
    use MagicFilters;
    
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'uiCombo',
        ];
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
        return $this->getMagicActivityTypeFilter($field);
    }
}
