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
        if (!$query->hasFilter('magicContext')) {
            return true;
        }

        // Only some magic filters support relational requests.
        $magicContext = $query->filter('magicContext');
        if (!$this->relationalMagicContext($magicContext)) {
            return true;
        }

        if (!empty($this->getMagicContextAgentFilter($magicContext))) {
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
        $magicContext = $query->filter('magicContext');
        $agents = resolve(AgentService::class)->addFilter([
            'magic' => $magicContext,
            'owner_id' => $ownerId
        ])->get();

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        // Modify the filters.
        $callback = $this->magicContextAgentCallback($agents);
        $query->removeFilter('magicContext');
        $query->addFilter(['agentRelations' => ['$has' => $callback]]);
        return true;
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @return callable
     */
    protected function magicContextAgentCallback(Collection $agents): callable
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
        $magicContext = $query->filter('magicContext');
        $prefix = \Str::before($magicContext, ':');
        if (in_array($prefix, ['parent', 'grouping', 'category'])) {
            $activityId = \Str::after($magicContext, $prefix.':');
        } else {
            $activityId = $magicContext;
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
        $callback = $this->magicContextActivityCallback($activity, $prefix);
        $query->removeFilter('magicContext');
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
    protected function magicContextActivityCallback(Activity $activity, $relation = null): callable
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
