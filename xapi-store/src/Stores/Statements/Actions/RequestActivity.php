<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Activities\Activity;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Relations\StatementActivity;

trait RequestActivity
{
    /**
     * Activity filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return bool
     */
    protected function requestActivity(Query $query): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('activity')
            || !config('trax-xapi-store.relations.statements_activities', false)
        ) {
            return true;
        }

        // Get the activity.
        $iri = $query->filter('activity');
        if (!$activityId = app(ActivityRepository::class)->idByIri($iri, $query)) {
            return false;
        }

        // Adapt the query.
        $callback = $query->hasOption('related_activities') && $query->option('related_activities') == 'true'
            ? $this->relatedActivitiesCallback($activityId)
            : $this->activityCallback($activityId);
        
        // Modify the filters.
        $query->removeFilter('activity');
        $query->addFilter(['id' => ['$in' => $callback]]);
        return true;
    }

    /**
     * Get callback for activity filter.
     *
     * @param  int  $activityId
     * @return callable
     */
    protected function activityCallback(int $activityId): callable
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
    protected function relatedActivitiesCallback(int $activityId): callable
    {
        return function ($query) use ($activityId) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->where('activity_id', $activityId);
        };
    }
}
