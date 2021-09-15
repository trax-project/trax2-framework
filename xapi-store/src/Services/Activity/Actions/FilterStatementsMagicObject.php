<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Relations\StatementActivity;

trait FilterStatementsMagicObject
{
    /**
     * Object filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function filterStatementsMagicObject(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('uiObject')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // The object is an agent.
        if ($this->repository->hasMagicAgentFilter($query->filter('uiObject'))) {
            return;
        }

        // Only some UI filters support relational requests.
        if (!$this->repository->relationalMagicActivity($query->filter('uiObject'))) {
            return;
        }

        // Get the matching axtivities.
        $activities = $this->repository->whereUiCombo($query->filter('uiObject'), $query);

        // No matching.
        if ($activities->isEmpty()) {
            throw new NotFoundHttpException();
        }

        $activityIds = $activities->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiObject');
        $query->addFilter([
            'id' => ['$in' => $this->filterStatementsMagicObjectCallback($activityIds)]
        ]);
    }

    /**
     * Get callback for activity filter.
     *
     * @param  \Illuminate\Support\Collection  $activityIds
     * @return callable
     */
    protected function filterStatementsMagicObjectCallback(Collection $activityIds): callable
    {
        return function ($query) use ($activityIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_activity')
                ->whereIn('activity_id', $activityIds)
                ->where('type', StatementActivity::TYPE_OBJECT);
        };
    }
}
