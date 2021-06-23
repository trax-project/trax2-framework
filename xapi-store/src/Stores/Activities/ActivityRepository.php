<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;
use Trax\XapiStore\Caching;

class ActivityRepository extends CrudRepository
{
    use MergeableModelRepo, ActivityFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Activities\ActivityFactory
     */
    public function factory()
    {
        return ActivityFactory::class;
    }

    /**
     * Find an existing activity given its IRI.
     *
     * @param  string  $iri
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function findByIri(string $iri, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        return Caching::activity($iri, function ($iri, $ownerId) {
            return $this->addFilter(['iri' => $iri, 'owner_id' => $ownerId])->get()->first();
        }, $ownerId);
    }

    /**
     * Find a collection of activities given their IRIs.
     *
     * @param  array  $iris
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereIriIn(array $iris, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        // We should use the caching system here!!!!!!!!!!!!!!!!!

        return $this->addFilter(['iri' => ['$in' => $iris], 'owner_id' => $ownerId])->get();
    }

    /**
     * Find activities given their uiCombo.
     *
     * @param  string  $uiCombo
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereUiCombo(string $uiCombo, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uiCombo' => $uiCombo, 'owner_id' => $ownerId])->get();
    }
}
