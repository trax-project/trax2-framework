<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Verbs\VerbRepository;

trait RequestMagicVerb
{
    /**
     * Verb filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function requestMagicVerb(Query $query): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('uiVerb')
            || !config('trax-xapi-store.tables.verbs', false)
            || !config('trax-xapi-store.relations.statements_verbs', false)
        ) {
            return true;
        }

        // Only some UI filters support relational requests.
        $uiVerb = $query->filter('uiVerb');
        if (!$this->relationalMagicVerb($uiVerb)) {
            return true;
        }

        // Get the matching verbs.
        $verbs = app(VerbRepository::class)->whereUiCombo($uiVerb, $query);

        // No matching.
        if ($verbs->isEmpty()) {
            return false;
        }

        $verbIds = $verbs->pluck('id');

        // Allow whereHas request when it is the only filter (better perfs).
        $whereHas = !$query->hasFilter('uiActor')
            && !$query->hasFilter('uiObject')
            && !$query->hasFilter('uiContext');
    
        // Modify the filters.
        $query->removeFilter('uiVerb');
        if ($whereHas) {
            $query->addFilter(['verbRelations' => ['$has' => $this->magicVerbWhereHasCallback($verbIds)]]);
        } else {
            $query->addFilter(['id' => ['$in' => $this->magicVerbWhereInCallback($verbIds)]]);
        }
        
        return true;
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $verbIds
     * @return callable
     */
    protected function magicVerbWhereInCallback(Collection $verbIds): callable
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
    protected function magicVerbWhereHasCallback(Collection $verbIds): callable
    {
        return function ($query) use ($verbIds) {
            return $query
                ->whereIn('verb_id', $verbIds)
                ->where('sub', false);
        };
    }
}
