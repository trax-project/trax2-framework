<?php

namespace Trax\Auth\Stores\Users;

use Trax\Repo\Querying\Query;

trait UserFilters
{
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'simpleSearch',
        ];
    }

    /**
     * Filter: simpleSearch.
     *
     * @param  string  $value
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function simpleSearchFilter($value, Query $query)
    {
        return ['$or' => [
            ['username' => ['$text' => $value]],
            ['email' => ['$text' => $value]],
            ['firstname' => ['$text' => $value]],
            ['lastname' => ['$text' => $value]],
        ]];
    }
}
