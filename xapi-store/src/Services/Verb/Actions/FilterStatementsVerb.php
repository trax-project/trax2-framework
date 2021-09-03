<?php

namespace Trax\XapiStore\Services\Verb\Actions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;

trait FilterStatementsVerb
{
    /**
     * Verb filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsVerb(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('verb')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // Verb not found.
        if (!$verbId = $this->repository->idByIri($query->filter('verb'), $query)) {
            throw new NotFoundHttpException();
        }

        // Allow whereHas request when it is the only filter (better perfs).
        $whereHas = !$query->hasFilter('agent') && !$query->hasFilter('activity');

        // Modify the filters.
        $query->removeFilter('verb');
        if ($whereHas) {
            $query->addFilter(['verbRelations' => ['$has' => $this->filterStatementsVerbWhereHasCallback($verbId)]]);
        } else {
            $query->addFilter(['id' => ['$in' => $this->filterStatementsVerbWhereInCallback($verbId)]]);
        }
    }

    /**
     * Get callback for verb filter.
     *
     * @param  int  $verbId
     * @return callable
     */
    protected function filterStatementsVerbWhereInCallback(int $verbId): callable
    {
        return function ($query) use ($verbId) {
            return $query->select('statement_id')->from('trax_xapi_statement_verb')
                ->where('verb_id', $verbId)
                ->where('sub', false);
        };
    }

    /**
     * Get callback for verb filter.
     *
     * @param  int  $verbId
     * @return callable
     */
    protected function filterStatementsVerbWhereHasCallback(int $verbId): callable
    {
        return function ($query) use ($verbId) {
            return $query
                ->where('verb_id', $verbId)
                ->where('sub', false);
        };
    }
}
