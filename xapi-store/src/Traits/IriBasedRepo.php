<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;

trait IriBasedRepo
{
    /**
     * Find an existing record given its IRI.
     *
     * @param  string  $iri
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function findByIri(string $iri, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['iri' => $iri, 'owner_id' => $ownerId])->get()->first();
    }

    /**
     * Find a collection of records given their IRIs.
     *
     * @param  array  $iris
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereIriIn(array $iris, Query $query = null): Collection
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['iri' => ['$in' => $iris], 'owner_id' => $ownerId])->get();
    }

    /**
     * Find records given their uiCombo.
     *
     * @param  string  $uiCombo
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereUiCombo(string $uiCombo, Query $query = null): Collection
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uiCombo' => $uiCombo, 'owner_id' => $ownerId])->get();
    }

    /**
     * Insert records and returns models.
     *
     * @param  array  $data
     * @return \Illuminate\Support\Collection
     */
    public function insertAndGet(array $data): Collection
    {
        $insertedBatch = $this->insert($data);

        // Get back the models.
        $iris = collect($insertedBatch)->pluck('iri')->toArray();
        $models = $this->whereIriIn($iris);

        // Add them to the cache.
        $this->cache($models);
        return $models;
    }

    /**
     * Cache a collection of records.
     *
     * @param  \Illuminate\Support\Collection  $records
     * @return void
     */
    public function cache(Collection $records): void
    {
    }
}
