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
        $voidingStatements = $statements->where('data.verb.id', 'http://adlnet.gov/expapi/verbs/voided');
        $this->voidStatements($voidingStatements->pluck('data.object.id')->all());

        // Release statements (pending status, data update).
        $this->releaseStatements($statements, $allowPseudo);
    }
}
