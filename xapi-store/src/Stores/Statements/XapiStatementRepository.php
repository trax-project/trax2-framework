<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Repo\Querying\Query;
use Trax\XapiValidation\Statement;

trait XapiStatementRepository
{
    /**
     * Finalize a resource before returning it.
     *
     * @param  \Illuminate\Database\Eloquent\Model|object  $resource
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|object
     */
    public function finalize($resource, Query $query = null)
    {
        // Get statement OBJECT. DB query builder returns json encoded data.
        $statement = $resource->data;
        if (is_string($statement)) {
            $statement = json_decode($statement);
        }

        // Early exit for performance improvement.
        if (!isset($query) || (!$query->hasOption('format') && !$query->hasOption('reorder'))) {
            $resource->data = $statement;
            return $resource;
        }

        // Format.
        $format = isset($query) ? $query->option('format', 'exact') : 'exact';
        $lang = isset($query) ? $query->option('lang') : null;
        $statement = Statement::format($statement, $format, $lang);

        // Reorder props for readability.
        $reorder = isset($query) ? $query->option('reorder') : false;
        if ($reorder) {
            $statement = Statement::reorderStatement($statement);
        }

        // Result.
        $resource->data = $statement;
        return $resource;
    }
}
