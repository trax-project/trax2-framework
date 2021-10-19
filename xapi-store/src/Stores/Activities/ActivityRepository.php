<?php

namespace Trax\XapiStore\Stores\Activities;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;
use Trax\XapiStore\Caching;
use Trax\XapiStore\Relations\StatementActivity;
use Trax\XapiStore\Traits\IriBasedRepo;

class ActivityRepository extends CrudRepository
{
    use IriBasedRepo, MergeableModelRepo, ActivityFilters;

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
