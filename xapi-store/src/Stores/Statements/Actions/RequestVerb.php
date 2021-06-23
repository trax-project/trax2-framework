<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Verbs\VerbRepository;

trait RequestVerb
{
    /**
     * Verb filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function requestVerb(Query $query = null): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('verb')
            || !config('trax-xapi-store.tables.verbs', false)
            || !config('trax-xapi-store.relations.statements_verbs', false)
        ) {
            return true;
        }

        // Get the verb.
        $iri = $query->filter('verb');
        if (!$verbId = app(VerbRepository::class)->idByIri($iri, $query)) {
            return false;
        }

        // Allow whereHas request when it is the only filter (better perfs).
        $whereHas = !$query->hasFilter('agent') && !$query->hasFilter('activity');

        // Modify the filters.
        $query->removeFilter('verb');
        if ($whereHas) {
            $query->addFilter(['verbRelations' => ['$has' => $this->verbWhereHasCallback($verbId)]]);
        } else {
            $query->addFilter(['id' => ['$in' => $this->verbWhereInCallback($verbId)]]);
        }

        return true;
    }

    /**
     * Get callback for verb filter.
     *
     * @param  int  $verbId
     * @return callable
     */
    protected function verbWhereInCallback(int $verbId): callable
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
    protected function verbWhereHasCallback(int $verbId): callable
    {
        return function ($query) use ($verbId) {
            return $query
                ->where('verb_id', $verbId)
                ->where('sub', false);
        };
    }
}
