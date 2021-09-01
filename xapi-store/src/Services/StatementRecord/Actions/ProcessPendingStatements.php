<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;

trait ProcessPendingStatements
{
    use VoidStatements, ReleaseStatements;

    /**
     * Process the pending statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  boolean  $allowPseudonymization
     * @return void
     */
    public function processPendingStatements(Collection $statements, bool $allowPseudonymization): void
    {
        if (config('trax-xapi-store.queues.agents.enabled', false)) {
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
        // Voiding.
        $voidingStatements = $statements->where('data.verb.id', 'http://adlnet.gov/expapi/verbs/voided');
        $this->voidStatements($voidingStatements->pluck('data.object.id')->all());

        // Release statements (pending status, data update).
        $this->releaseStatements($statements, $allowPseudonymization);
    }
}
