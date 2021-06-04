<?php

namespace Trax\XapiStore\Stores\Verbs;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;

trait VerbFilters
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
        return $this->getMagicVerbFilter($field);
    }
}
