<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Auth\TraxAuth;
use Trax\Repo\CrudRepository;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Caching;

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
     * Find an existing agent given its VID.
     *
     * @param  string  $vid
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function findByVid(string $vid, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        return Caching::agent($vid, function ($vid, $ownerId) {
            return $this->addFilter(['vid' => $vid, 'owner_id' => $ownerId])->get()->first();
        }, $ownerId);
    }

    /**
     * Find a collection of agents given their VIDs.
     *
     * @param  array  $vids
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereVidIn(array $vids, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        // We should use the caching system here!!!!!!!!!!!!!!!!!

        return $this->addFilter(['vid' => ['$in' => $vids], 'owner_id' => $ownerId])->get();
    }

    /**
     * Find agents given their uiCombo.
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
