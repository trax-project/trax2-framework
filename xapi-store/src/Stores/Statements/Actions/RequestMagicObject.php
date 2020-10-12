<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentRepository;
use Trax\XapiStore\Stores\Activities\ActivityRepository;

trait RequestMagicObject
{
    /**
     * Object filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicObject(Query $query = null, $ownerId = null, bool $reveal = true): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('magicObject')) {
            return true;
        }
        if ($this->hasMagicAgentFilter($query->filter('magicObject'))) {
            return $this->requestMagicObjectAgent($query, $ownerId, $reveal);
        } else {
            return $this->requestMagicObjectActivity($query, $ownerId);
        }
    }

    /**
     * Object filtering by agent.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicObjectAgent(Query $query = null, $ownerId = null, bool $reveal = true): bool
    {
        // We can't make a relational request.
        if (!$reveal
            || !config('trax-xapi-store.tables.agents', false)
            || !config('trax-xapi-store.relations.statements_agents', false)
        ) {
            return true;
        }

        // Only some magic filters support relational requests.
        $magicObject = $query->filter('magicObject');
        if (!$this->relationalMagicAgent($magicObject)) {
            return true;
        }

        // Get the matching agents.
        $agents = resolve(AgentRepository::class)->addFilter([
            'magic' => $magicObject,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        // Modify the filters.
        $callback = $this->magicObjectAgentCallback($agents);
        $query->removeFilter('magicObject');
        $query->addFilter(['agentRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @return callable
     */
    protected function magicObjectAgentCallback(Collection $agents): callable
    {
        return function ($query) use ($agents) {
            return $query
                ->whereIn('agent_id', $agents->pluck('id'))
                ->where('type', 'object');
        };
    }

    /**
     * Object filtering by activity.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @return bool
     */
    protected function requestMagicObjectActivity(Query $query = null, $ownerId = null): bool
    {
        // We can't make a relational request.
        if (!config('trax-xapi-store.relations.statements_activities', false)) {
            return true;
        }

        // Only some magic filters support relational requests.
        $magicObject = $query->filter('magicObject');
        if (!$this->relationalMagicActivity($magicObject)) {
            return true;
        }

        // Get the matching axtivities.
        $activities = resolve(ActivityRepository::class)->addFilter([
            'magic' => $magicObject,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($activities->isEmpty()) {
            return false;
        }

        // Modify the filters.
        $callback = $this->magicObjectActivityCallback($activities);
        $query->removeFilter('magicObject');
        $query->addFilter(['activityRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for activity filter.
     *
     * @param  \Illuminate\Support\Collection  $activities
     * @return callable
     */
    protected function magicObjectActivityCallback(Collection $activities): callable
    {
        return function ($query) use ($activities) {
            return $query
                ->whereIn('activity_id', $activities->pluck('id'))
                ->where('type', 'object');
        };
    }
}
