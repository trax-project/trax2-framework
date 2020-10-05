<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Repo\Querying\Query;

trait XapiActivityFilters
{
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'activityId',
        ];
    }

    /**
     * Filter: activityId.
     *
     * @param  string  $id
     * @param  \Trax\Repo\Querying\Query|null  $query
     * @return array
     */
    public function activityIdFilter($id, Query $query = null)
    {
        return [
            ['iri' => $id],
        ];
    }
}
