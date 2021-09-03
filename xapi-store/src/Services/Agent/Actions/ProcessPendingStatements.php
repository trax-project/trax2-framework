<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Illuminate\Support\Collection;

trait ProcessPendingStatements
{
    use RecordStatementsAgents, PseudonymizeStatements;
    
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
        $agents = $this->recordStatementsAgents($statements);
        $statements = $this->pseudonymizeStatements($statements, $agents, $allowPseudonymization);

        app(\Trax\XapiStore\Services\Verb\VerbService::class)
            ->processPendingStatements($statements, $allowPseudonymization);
    }
}
