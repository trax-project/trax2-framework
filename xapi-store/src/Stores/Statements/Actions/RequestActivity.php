<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Activities\Activity;
use Trax\XapiStore\Stores\Activities\ActivityRepository;

trait RequestActivity
{
    /**
     * Activity filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @return bool
     */
    protected function requestActivity(Query $query = null, $ownerId = null): bool
    {
        // We can't make a relational request.
        if (!$query->hasFilter('activity')
            || !config('trax-xapi-store.relations.statements_activities', false)
        ) {
            return true;
        }

        // Get the activity.
        $activities = resolve(ActivityRepository::class);
        if (!$activity = $activities->addFilter([
            'iri' => $query->filter('activity'),
            'owner_id' => $ownerId
        ])->get()->first()) {
            // No matching.
            return false;
        }

        // Adapt the query.
        $callback = $query->hasOption('related_activities') && $query->option('related_activities') == 'true'
            ? $this->relatedActivitiesCallback($activity)
            : $this->activityCallback($activity);
        
        // Modify the filters.
        $query->removeFilter('activity');
        $query->addFilter(['id' => ['$in' => $callback]]);
        return true;
    }

    /**
     * Get callback for activity filter.
     *
     * @param  \Trax\XapiStore\Stores\Activities\Activity  $activity
     * @return callable
     */
    protected function activityCallback(Activity $activity): callable
    {
        return function ($query) use ($activity) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->where('activity_id', $activity->id)
                ->where('type', 'object')
                ->where('sub', false);
        };
    }

    /**
     * Get callback for related activities filter.
     *
     * @param  \Trax\XapiStore\Stores\Activities\Activity  $activity
     * @return callable
     */
    protected function relatedActivitiesCallback(Activity $activity): callable
    {
        return function ($query) use ($activity) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->where('activity_id', $activity->id);
        };
    }
}
