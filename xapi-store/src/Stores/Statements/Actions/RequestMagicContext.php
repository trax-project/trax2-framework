<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentService;
use Trax\XapiStore\Stores\Activities\Activity;
use Trax\XapiStore\Stores\Activities\ActivityRepository;

trait RequestMagicContext
{
    /**
     * Context filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicContext(Query $query = null, $ownerId = null, bool $reveal = true): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('uiContext')) {
            return true;
        }

        // Only some UI filters support relational requests.
        $uiContext = $query->filter('uiContext');
        if (!$this->relationalMagicContext($uiContext)) {
            return true;
        }

        if (!empty($this->getMagicContextAgentFilter($uiContext))) {
            return $this->requestMagicContextAgent($query, $ownerId, $reveal);
        } else {
            return $this->requestMagicContextActivity($query, $ownerId);
        }
    }

    /**
     * Context filtering by agent.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicContextAgent(Query $query = null, $ownerId = null, bool $reveal = true): bool
    {
        // We can't make a relational request.
        if (!$reveal
            || !config('trax-xapi-store.tables.agents', false)
            || !config('trax-xapi-store.relations.statements_agents', false)
        ) {
            return true;
        }

        // Get the matching agents.
        $uiContext = $query->filter('uiContext');
        $agents = resolve(AgentService::class)->addFilter([
            'uiCombo' => $uiContext,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        // Modify the filters.
        $callback = $this->uiContextAgentCallback($agents);
        $query->removeFilter('uiContext');
        $query->addFilter(['agentRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @return callable
     */
    protected function uiContextAgentCallback(Collection $agents): callable
    {
        return function ($query) use ($agents) {
            return $query
                ->whereIn('agent_id', $agents->pluck('id'))
                ->whereIn('type', ['instructor', 'team']);
        };
    }

    /**
     * Context filtering by activity.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @return bool
     */
    protected function requestMagicContextActivity(Query $query = null, $ownerId = null): bool
    {
        // We can't make a relational request.
        if (!config('trax-xapi-store.relations.statements_activities', false)) {
            return true;
        }

        // Get the matching activities.
        $uiContext = $query->filter('uiContext');
        $prefix = \Str::before($uiContext, ':');
        if (in_array($prefix, ['parent', 'grouping', 'category'])) {
            $activityId = \Str::after($uiContext, $prefix.':');
        } else {
            $activityId = $uiContext;
            $prefix = null;
        }
        $activity = resolve(ActivityRepository::class)->addFilter([
            'iri' => $activityId,
            'owner_id' => $ownerId
        ])->get()->first();

        // No matching.
        if (!$activity) {
            return false;
        }

        // Modify the filters.
        $callback = $this->uiContextActivityCallback($activity, $prefix);
        $query->removeFilter('uiContext');
        $query->addFilter(['activityRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for activity filter.
     *
     * @param  \Trax\XapiStore\Stores\Activities\Activity  $activity
     * @param  string  $prefix
     * @return callable
     */
    protected function uiContextActivityCallback(Activity $activity, $relation = null): callable
    {
        return function ($query) use ($activity, $relation) {
            if (!isset($relation)) {
                return $query
                    ->where('activity_id', $activity->id)
                    ->whereIn('type', ['parent', 'grouping', 'category', 'other']);
            } else {
                return $query
                    ->where('activity_id', $activity->id)
                    ->where('type', $relation);
            }
        };
    }
}
