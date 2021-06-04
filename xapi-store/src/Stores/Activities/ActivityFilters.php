<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;

trait ActivityFilters
{
    use MagicFilters;
    use XapiActivityFilters {
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
            'uiId',
            'uiType',
            'uiName',
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
        return $this->getMagicActivityFilter($field);
    }

    /**
     * Filter: uiId.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiIdFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicIriFilter($field, 'iri');
    }

    /**
     * Filter: uiType.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiTypeFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicIriFilter($field, 'data->definition->type');
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
            ['data->definition->name' => ['$text' => $field]],
        ];
    }
}
