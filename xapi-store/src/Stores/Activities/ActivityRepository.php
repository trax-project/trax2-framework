<?php

namespace Trax\XapiStore\Stores\Activities;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;
use Trax\XapiStore\Caching;
use Trax\XapiStore\Relations\StatementActivity;

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
     * Cache a collection of activities.
     *
     * @param  \Illuminate\Support\Collection  $activities
     * @return void
     */
    public function cache(Collection $activities): void
    {
        Caching::cacheActivities(
            $activities->pluck('iri', 'id'),
            TraxAuth::context('owner_id')
        );
    }

    /**
     * Find an existing activity ID given its IRI.
     *
     * @param  string  $iri
     * @param  \Trax\Repo\Querying\Query  $query
     * @return int|false
     */
    public function idByIri(string $iri, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        return Caching::activityId($iri, function ($iri, $ownerId) {
            $activity = $this->addFilter(['iri' => $iri, 'owner_id' => $ownerId])->get()->first();
            return $activity ? $activity->id : false;
        }, $ownerId);
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
        return $this->addFilter(['iri' => $iri, 'owner_id' => $ownerId])->get()->first();
    }

    /**
     * Find a collection of activities given their IRIs.
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
     * Find activities given their uiCombo.
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
     * Insert activities and returns models.
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
     * Insert relations between activities and statements.
     *
     * @param  array  $data
     * @return void
     */
    public function insertStatementsRelations(array $data): void
    {
        StatementActivity::insert($data);
    }
}
