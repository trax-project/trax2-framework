<?php

namespace Trax\XapiStore\Stores\Agents;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\CrudRepository;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Caching;
use Trax\XapiStore\Relations\StatementAgent;

class AgentRepository extends CrudRepository
{
    use AgentFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Agents\AgentFactory
     */
    public function factory()
    {
        return AgentFactory::class;
    }

    /**
     * Cache a collection of agents.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @return void
     */
    public function cache(Collection $agents): void
    {
        Caching::cacheAgents(
            $agents->pluck('vid', 'id'),
            TraxAuth::context('owner_id')
        );
    }

    /**
     * Find an existing agent ID given its VID.
     *
     * @param  string  $vid
     * @param  \Trax\Repo\Querying\Query  $query
     * @return int|false
     */
    public function idByVid(string $vid, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        return Caching::agentId($vid, function ($vid, $ownerId) {
            $agent = $this->addFilter(['vid' => $vid, 'owner_id' => $ownerId])->get()->first();
            return $agent ? $agent->id : false;
        }, $ownerId);
    }

    /**
     * Find an existing agent given its VID.
     *
     * @param  string  $vid
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function findByVid(string $vid, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['vid' => $vid, 'owner_id' => $ownerId])->get()->first();
    }

    /**
     * Find a collection of agents given their VIDs.
     *
     * @param  array  $vids
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereVidIn(array $vids, Query $query = null): Collection
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['vid' => ['$in' => $vids], 'owner_id' => $ownerId])->get();
    }

    /**
     * Find agents given their uiCombo.
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
     * Insert agents and returns models.
     *
     * @param  array  $data
     * @return \Illuminate\Support\Collection
     */
    public function insertAndGet(array $data): Collection
    {
        $insertedBatch = $this->insert($data);

        // Get back the models.
        $vids = collect($insertedBatch)->pluck('vid')->toArray();
        $models = $this->whereVidIn($vids);

        // Add them to the cache.
        $this->cache($models);
        return $models;
    }

    /**
     * Insert relations between verbs and statements.
     *
     * @param  array  $data
     * @return void
     */
    public function insertStatementsRelations(array $data): void
    {
        StatementAgent::insert($data);
    }
}
