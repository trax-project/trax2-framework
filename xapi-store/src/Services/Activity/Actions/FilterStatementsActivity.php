<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Relations\StatementActivity;

trait FilterStatementsActivity
{
    /**
     * Activity filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsActivity(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('activity')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // Activity not found.
        if (!$activityId = $this->repository->idByIri($query->filter('activity'), $query)) {
            throw new NotFoundHttpException();
        }

        // Adapt the query.
        $callback = $query->hasOption('related_activities') && $query->option('related_activities') == 'true'
            ? $this->filterStatementsRelatedActivitiesCallback($activityId)
            : $this->filterStatementsActivityCallback($activityId);
        
        // Modify the filters.
        $query->removeFilter('activity');
        $query->addFilter(['id' => ['$in' => $callback]]);
    }

    /**
     * Get callback for activity filter.
     *
     * @param  int  $activityId
     * @return callable
     */
    protected function filterStatementsActivityCallback(int $activityId): callable
    {
        return function ($query) use ($activityId) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->where('activity_id', $activityId)
                ->where('type', StatementActivity::TYPE_OBJECT)
                ->where('sub', false);
        };
    }

    /**
     * Get callback for related activities filter.
     *
     * @param  int  $activityId
     * @return callable
     */
    protected function filterStatementsRelatedActivitiesCallback(int $activityId): callable
    {
        return function ($query) use ($activityId) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->where('activity_id', $activityId);
        };
    }
}
