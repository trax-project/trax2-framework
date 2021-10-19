<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Relations\StatementActivity;

trait FilterStatementsMagicContext
{
    /**
     * Context filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsMagicContext(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('uiContext')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // Only some UI filters support relational requests.
        if (!$this->repository->relationalMagicContext($query->filter('uiContext'))) {
            return;
        }

        // The context is an agent.
        if ($this->repository->hasMagicContextAgentFilter($query->filter('uiContext'))) {
            return;
        }

        // Get the matching activities.
        $uiContext = $query->filter('uiContext');
        $prefix = \Str::before($uiContext, ':');
        if (in_array($prefix, ['parent', 'grouping', 'category', 'other'])) {
            $activityId = \Str::after($uiContext, $prefix.':');
            $type = StatementActivity::contextByName($prefix);
        } else {
            $activityId = $uiContext;
            $type = null;
        }

        // No matching.
        if (!$activityId = $this->repository->idByIri($activityId, $query)) {
            throw new NotFoundHttpException();
        }

        // Modify the filters.
        $query->removeFilter('uiContext');
        $query->addFilter([
            'id' => ['$in' => $this->filterStatementsMagicContextCallback($activityId, $type)]
        ]);
    }

    /**
     * Get callback for activity filter.
     *
     * @param  int  $activityId
     * @param  int  $type
     * @return callable
     */
    protected function filterStatementsMagicContextCallback(int $activityId, $type = null): callable
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
                    ]);
            } else {
                return $query->select('statement_id')->from('trax_xapi_statement_activity')
                    ->where('activity_id', $activityId)
                    ->where('type', $type);
            }
        };
    }
}
