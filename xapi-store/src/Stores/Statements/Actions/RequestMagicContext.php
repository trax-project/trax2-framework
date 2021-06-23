<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
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
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicContext(Query $query = null, bool $reveal = true): bool
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
            return $this->requestMagicContextAgent($query, $reveal);
        } else {
            return $this->requestMagicContextActivity($query);
        }
    }

    /**
     * Context filtering by agent.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicContextAgent(Query $query = null, bool $reveal = true): bool
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
        $agents = app(AgentService::class)->whereUiCombo($uiContext, $query);

        // No matching.
        if ($agents->isEmpty()) {
            return false;
        }

        $agentIds = $agents->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiContext');
        $query->addFilter(['id' => ['$in' => $this->magicContextAgentCallback($agentIds)]]);

        return true;
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Illuminate\Support\Collection  $agentIds
     * @return callable
     */
    protected function magicContextAgentCallback(Collection $agentIds): callable
    {
        return function ($query) use ($agentIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->whereIn('agent_id', $agentIds)
                ->whereIn('type', ['instructor', 'team'])
                ->where('sub', false);
        };
    }

    /**
     * Context filtering by activity.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function requestMagicContextActivity(Query $query = null): bool
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

        if (!$activity = app(ActivityRepository::class)->findByIri($activityId, $query)) {
            return false;
        }

        // Modify the filters.
        $query->removeFilter('uiContext');
        $query->addFilter([
            'id' => ['$in' => $this->magicContextActivityCallback($activity, $prefix)]
        ]);
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
                return $query->select('statement_id')->from('trax_xapi_statement_activity')
                    ->where('activity_id', $activity->id)
                    ->whereIn('type', ['parent', 'grouping', 'category', 'other'])
                    ->where('sub', false);
            } else {
                return $query->select('statement_id')->from('trax_xapi_statement_activity')
                    ->where('activity_id', $activity->id)
                    ->where('type', $relation)
                    ->where('sub', false);
            }
        };
    }
}
