<?php

namespace Trax\XapiStore\Services\Verb\Actions;

use Illuminate\Support\Collection;

trait ProcessPendingStatements
{
    use RecordStatementsVerbs;
    
    /**
     * Process the pending statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  boolean  $allowPseudonymization
     * @return void
     */
    public function processPendingStatements(Collection $statements, bool $allowPseudonymization): void
    {
        if (config('trax-xapi-store.queues.verbs.enabled', false)) {
            $this->startProcessingJob($statements);
        } else {
            $this->processStatementsNow($statements, $allowPseudonymization);
        }
    }

    /**
     * Start a processing job.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    protected function startProcessingJob(Collection $statements): void
    {
    }

    /**
     * Process a batch of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  boolean  $allowPseudonymization
     * @return void
     */
    protected function processStatementsNow(Collection $statements, bool $allowPseudonymization): void
    {
        $this->recordStatementsVerbs($statements);

        app(\Trax\XapiStore\Services\StatementRecord\StatementRecordService::class)
            ->processPendingStatements($statements, $allowPseudonymization);
    }
}
