<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentService;
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
        if (!$query->hasFilter('uiObject')) {
            return true;
        }
        if ($this->hasMagicAgentFilter($query->filter('uiObject'))) {
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

        // Only some UI filters support relational requests.
        $uiObject = $query->filter('uiObject');
        if (!$this->relationalMagicAgent($uiObject)) {
            return true;
        }

        // Get the matching agents.
        $agents = resolve(AgentService::class)->addFilter([
            'uiCombo' => $uiObject,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        $agentIds = $agents->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiObject');
        $query->addFilter(['id' => ['$in' => $this->magicObjectAgentCallback($agentIds)]]);

        return true;
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $agentIds
     * @return callable
     */
    protected function magicObjectAgentCallback(Collection $agentIds): callable
    {
        return function ($query) use ($agentIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->whereIn('agent_id', $agentIds)
                ->where('type', 'object')
                ->where('sub', false);
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

        // Only some UI filters support relational requests.
        $uiObject = $query->filter('uiObject');
        if (!$this->relationalMagicActivity($uiObject)) {
            return true;
        }

        // Get the matching axtivities.
        $activities = resolve(ActivityRepository::class)->addFilter([
            'uiCombo' => $uiObject,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($activities->isEmpty()) {
            return false;
        }

        $activityIds = $activities->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiObject');
        $query->addFilter([
            'id' => ['$in' => $this->magicObjectActivityCallback($activityIds)]
        ]);
        return true;
    }

    /**
     * Get callback for activity filter.
     *
     * @param  \Illuminate\Support\Collection  $activityIds
     * @return callable
     */
    protected function magicObjectActivityCallback(Collection $activityIds): callable
    {
        return function ($query) use ($activityIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->whereIn('activity_id', $activityIds)
                ->where('type', 'object')
                ->where('sub', false);
        };
    }
}
