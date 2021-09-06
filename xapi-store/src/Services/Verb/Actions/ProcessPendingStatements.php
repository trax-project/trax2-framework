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
     * @return void
     */
    public function processPendingStatements(Collection $statements): void
    {
        $this->recordStatementsVerbs($statements);
    }
}
