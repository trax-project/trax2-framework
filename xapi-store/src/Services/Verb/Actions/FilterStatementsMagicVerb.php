<?php

namespace Trax\XapiStore\Services\Verb\Actions;

use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;

trait FilterStatementsMagicVerb
{
    /**
     * Verb filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsMagicVerb(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('uiVerb')
            || !config('trax-xapi-store.tables.verbs', false)
            || !config('trax-xapi-store.relations.statements_verbs', false)
        ) {
            return;
        }

        // Only some UI filters support relational requests.
        $uiVerb = $query->filter('uiVerb');
        if (!$this->repository->relationalMagicVerb($uiVerb)) {
            return;
        }

        // Get the matching verbs.
        $verbs = $this->repository->whereUiCombo($uiVerb, $query);

        // No matching.
        if ($verbs->isEmpty()) {
            throw new NotFoundHttpException();
        }

        $verbIds = $verbs->pluck('id');

        // Allow whereHas request when it is the only filter (better perfs).
        $whereHas = !$query->hasFilter('uiActor')
            && !$query->hasFilter('uiObject')
            && !$query->hasFilter('uiContext');
    
        // Modify the filters.
        $query->removeFilter('uiVerb');
        if ($whereHas) {
            $query->addFilter(['verbRelations' => ['$has' => $this->filterStatementsMagicVerbWhereHasCallback($verbIds)]]);
        } else {
            $query->addFilter(['id' => ['$in' => $this->filterStatementsMagicVerbWhereInCallback($verbIds)]]);
        }
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $verbIds
     * @return callable
     */
    protected function filterStatementsMagicVerbWhereInCallback(Collection $verbIds): callable
    {
        return function ($query) use ($verbIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_verb')
                ->whereIn('verb_id', $verbIds)
                ->where('sub', false);
        };
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $verbIds
     * @return callable
     */
    protected function filterStatementsMagicVerbWhereHasCallback(Collection $verbIds): callable
    {
        return function ($query) use ($verbIds) {
            return $query
                ->whereIn('verb_id', $verbIds)
                ->where('sub', false);
        };
    }
}
