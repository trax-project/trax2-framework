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
     * @param  boolean  $allowPseudo
     * @return void
     */
    public function processPendingStatements(Collection $statements, bool $allowPseudo): void
    {
        // Voiding.
        $this->processVoidingStatements($statements);

        // Release statements (pending status, data update).
        $this->releaseStatements($statements, $allowPseudo);
    }
}
