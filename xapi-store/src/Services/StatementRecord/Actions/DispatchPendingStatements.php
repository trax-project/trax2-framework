<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\Services\StatementRecord\Jobs\DispatchPendingStatementsJob;
use Trax\Auth\TraxAuth;

trait DispatchPendingStatements
{
    /**
     * Process the pending statements.
     *
     * @param  array  $uuids
     * @param  boolean  $allowPseudo
     * @param  boolean  $allowQueue
     * @return void
     */
    protected function dispatchPendingStatements(array $uuids, bool $allowPseudo = true, bool $allowQueue = true): void
    {
        if ($allowQueue
            && config('trax-xapi-store.queues.statements.enabled', false)
            && config('queue.default', 'sync') != 'sync'
        ) {
            DispatchPendingStatementsJob::dispatch(
                TraxAuth::context('owner_id')
            );
        } else {
            $this->processStatementsBatch(
                app(\Trax\XapiStore\Stores\Statements\StatementRepository::class)->whereUuidIn($uuids),
                $allowPseudo
            );
        }
    }

    /**
     * Process a batch of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  boolean  $allowPseudo
     * @return void
     */
    protected function processStatementsBatch(Collection $statements, bool $allowPseudo = true): void
    {
        app(\Trax\XapiStore\Services\Activity\ActivityService::class)->processPendingStatements($statements);
        if (config('trax-xapi-store.requests.relational', false)) {
            app(\Trax\XapiStore\Services\Agent\AgentService::class)->processPendingStatements($statements, $allowPseudo);
            app(\Trax\XapiStore\Services\Verb\VerbService::class)->processPendingStatements($statements);
        }
        app(\Trax\XapiStore\Services\StatementRecord\StatementRecordService::class)->processPendingStatements($statements, $allowPseudo);
    }
}
