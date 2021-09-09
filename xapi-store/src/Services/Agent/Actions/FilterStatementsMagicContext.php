<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Relations\StatementAgent;

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
    protected function filterStatementsMagicObject(Query $query): void
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

        // Not an agent filter.
        if (!$this->repository->hasMagicContextAgentFilter($query->filter('uiContext'))) {
            return;
        }

        // Get the matching agents.
        $agents = $this->repository->whereUiCombo($query->filter('uiContext'), $query);

        // No matching.
        if ($agents->isEmpty()) {
            throw new NotFoundHttpException();
        }

        $agentIds = $agents->pluck('id');

        // Modify the filters.
        $query->removeFilter('uiContext');
        $query->addFilter(['id' => ['$in' => $this->filterStatementsMagicObjectCallback($agentIds)]]);
    }

    /**
     * Get callback for agent filter.
     *
     * @param  \Illuminate\Support\Collection  $agentIds
     * @return callable
     */
    protected function filterStatementsMagicObjectCallback(Collection $agentIds): callable
    {
        return function ($query) use ($agentIds) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->whereIn('agent_id', $agentIds)
                ->whereIn('type', [StatementAgent::TYPE_INSTRUCTOR, StatementAgent::TYPE_TEAM])
                ->where('sub', false);
        };
    }
}
