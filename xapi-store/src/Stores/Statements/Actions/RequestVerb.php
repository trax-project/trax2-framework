<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Verbs\Verb;
use Trax\XapiStore\Stores\Verbs\VerbRepository;

trait RequestVerb
{
    /**
     * Verb filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @return bool
     */
    protected function requestVerb(Query $query = null, $ownerId = null): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('verb')
            || !config('trax-xapi-store.tables.verbs', false)
            || !config('trax-xapi-store.relations.statements_verbs', false)
        ) {
            return true;
        }

        // Get the verb.
        $verbs = resolve(VerbRepository::class);
        if (!$verb = $verbs->addFilter([
            'iri' => $query->filter('verb'),
            'owner_id' => $ownerId
        ])->get()->first()) {
            // No matching.
            return false;
        }

        // Allow whereHas request when it is the only filter (better perfs).
        $whereHas = !$query->hasFilter('agent') && !$query->hasFilter('activity');

        // Modify the filters.
        $query->removeFilter('verb');
        if ($whereHas) {
            $query->addFilter(['verbRelations' => ['$has' => $this->verbWhereHasCallback($verb)]]);
        } else {
            $query->addFilter(['id' => ['$in' => $this->verbWhereInCallback($verb)]]);
        }

        return true;
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Trax\XapiStore\Stores\Verbs\Verb  $verb
     * @return callable
     */
    protected function verbWhereInCallback(Verb $verb): callable
    {
        return function ($query) use ($verb) {
            return $query->select('statement_id')->from('trax_xapi_statement_verb')
                ->where('verb_id', $verb->id)
                ->where('sub', false);
        };
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Trax\XapiStore\Stores\Verbs\Verb  $verb
     * @return callable
     */
    protected function verbWhereHasCallback(Verb $verb): callable
    {
        return function ($query) use ($verb) {
            return $query
                ->where('verb_id', $verb->id)
                ->where('sub', false);
        };
    }
}
