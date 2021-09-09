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
     * @param  boolean  $allowPseudo
     * @return void
     */
    public function processPendingStatements(Collection $statements, bool $allowPseudo): void
    {
        $agents = $this->recordStatementsAgents($statements);
        $this->pseudonymizeStatements($statements, $agents, $allowPseudo);
    }
}
