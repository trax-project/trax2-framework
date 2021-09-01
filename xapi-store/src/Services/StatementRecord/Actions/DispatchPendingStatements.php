<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;

trait DispatchPendingStatements
{
    /**
     * Process the pending statements.
     *
     * @param  array  $uuids
     * @param  boolean  $allowPseudonymization
     * @return void
     */
    protected function dispatchPendingStatements(array $uuids, bool $allowPseudonymization = true): void
    {
        if (config('trax-xapi-store.queues.statements.enabled', false)) {
            $this->startStatementsDispatching();
        } else {
            $this->processStatementsBatch(
                $this->repository->whereUuidIn($uuids),
                $allowPseudonymization
            );
        }
    }

    /**
     * Displatch statements.
     *
     * @return void
     */
    protected function startStatementsDispatching(): void
    {
    }

    /**
     * Process a batch of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  boolean  $allowPseudonymization
     * @return void
     */
    protected function processStatementsBatch(Collection $statements, bool $allowPseudonymization): void
    {
        // We start chained processing with activities, agents, verbs,
        // and finally with statements (voiding and pending status change).
        app(\Trax\XapiStore\Services\Activity\ActivityService::class)
            ->processPendingStatements($statements, $allowPseudonymization);
    }
}
