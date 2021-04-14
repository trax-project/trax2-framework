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
            'magic',
            'xapiId',
            'xapiType',
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
        return $this->getMagicActivityFilter($field);
    }

    /**
     * Filter: xapiId.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function xapiIdFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicIriFilter($field, 'iri');
    }

    /**
     * Filter: xapiType.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function xapiTypeFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicIriFilter($field, 'data->definition->type');
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
            ['data->definition->name' => ['$text' => $field]],
        ];
    }
}
