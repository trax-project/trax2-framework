<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Verbs\VerbRepository;

trait RequestMagicVerb
{
    /**
     * Verb filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @return bool
     */
    protected function requestMagicVerb(Query $query = null, $ownerId = null): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('magicVerb')
            || !config('trax-xapi-store.tables.verbs', false)
            || !config('trax-xapi-store.relations.statements_verbs', false)
        ) {
            return true;
        }

        // Only some magic filters support relational requests.
        $magicVerb = $query->filter('magicVerb');
        if (!$this->relationalMagicVerb($magicVerb)) {
            return true;
        }

        // Get the matching verbs.
        $verbs = resolve(VerbRepository::class)->addFilter([
            'magic' => $magicVerb,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($verbs->isEmpty()) {
            return false;
        }

        // Modify the filters.
        $callback = $this->magicVerbCallback($verbs);
        $query->removeFilter('magicVerb');
        $query->addFilter(['verbRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $verbs
     * @return callable
     */
    protected function magicVerbCallback(Collection $verbs): callable
    {
        return function ($query) use ($verbs) {
            return $query
                ->whereIn('verb_id', $verbs->pluck('id'));
        };
    }
}
