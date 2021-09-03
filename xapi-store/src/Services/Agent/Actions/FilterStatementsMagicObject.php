<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Relations\StatementAgent;

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
    protected function filterStatementsMagicContext(Query $query): void
    {
        // We can't make a relational request.
        if (!$query->hasFilter('uiObject')
            || !config('trax-xapi-store.requests.relational', false)
        ) {
            return;
        }

        // Not an agent filter.
        if (!$this->repository->hasMagicAgentFilter($query->filter('uiObject'))) {
            return;
        }

        // Only some UI filters support relational requests.
        if (!$this->repository->relationalMagicAgent($query->filter('uiObject'))) {
            return;
        }

        // Get the matching agents.
        $agents = $this->repository->whereUiCombo($query->filter('uiObject'), $query);
        if ($agents->isEmpty()) {
            throw new NotFoundHttpException();
        }
        $agentIds = $agents->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiObject');
        $query->addFilter(['id' => ['$in' => $this->filterStatementsMagicContextCallback($agentIds)]]);
    }

    /**
     * Get callback for verb filter.
     *
     * @param  \Illuminate\Support\Collection  $agentIds
     * @return callable
     */
    protected function filterStatementsMagicContextCallback(Collection $agentIds): callable
    {
        return function ($query) use ($agentIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->whereIn('agent_id', $agentIds)
                ->where('type', StatementAgent::TYPE_OBJECT)
                ->where('sub', false);
        };
    }
}
