<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Agents\AgentService;
use Trax\XapiStore\Stores\Activities\Activity;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Relations\StatementAgent;
use Trax\XapiStore\Relations\StatementActivity;

trait RequestMagicContext
{
    /**
     * Context filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  bool  $reveal
     * @return bool
     */
    protected function requestMagicContext(Query $query, bool $reveal = true): bool
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
    protected function requestMagicContextAgent(Query $query, bool $reveal = true): bool
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
                ->whereIn('type', [StatementAgent::TYPE_INSTRUCTOR, StatementAgent::TYPE_TEAM])
                ->where('sub', false);
        };
    }

    /**
     * Context filtering by activity.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function requestMagicContextActivity(Query $query): bool
    {
        // We can't make a relational request.
        if (!config('trax-xapi-store.relations.statements_activities', false)) {
            return true;
        }

        // Get the matching activities.
        $uiContext = $query->filter('uiContext');
        $prefix = \Str::before($uiContext, ':');
        if (in_array($prefix, ['parent', 'grouping', 'category', 'other'])) {
            $activityId = \Str::after($uiContext, $prefix.':');
            $type = StatementActivity::typeByName($prefix);
        } else {
            $activityId = $uiContext;
            $type = null;
        }

        if (!$activityId = app(ActivityRepository::class)->idByIri($activityId, $query)) {
            return false;
        }

        // Modify the filters.
        $query->removeFilter('uiContext');
        $query->addFilter([
            'id' => ['$in' => $this->magicContextActivityCallback($activityId, $type)]
        ]);
        return true;
    }

    /**
     * Get callback for activity filter.
     *
     * @param  int  $activityId
     * @param  int  $type
     * @return callable
     */
    protected function magicContextActivityCallback(int $activityId, $type = null): callable
    {
        return function ($query) use ($activityId, $type) {
            if (!isset($type)) {
                return $query->select('statement_id')->from('trax_xapi_statement_activity')
                    ->where('activity_id', $activityId)
                    ->whereIn('type', [
                        StatementActivity::TYPE_CONTEXT_PARENT,
                        StatementActivity::TYPE_CONTEXT_GROUPING,
                        StatementActivity::TYPE_CONTEXT_CATEGORY,
                        StatementActivity::TYPE_CONTEXT_OTHER
                    ])
                    ->where('sub', false);
            } else {
                return $query->select('statement_id')->from('trax_xapi_statement_activity')
                    ->where('activity_id', $activityId)
                    ->where('type', $type)
                    ->where('sub', false);
            }
        };
    }
}
