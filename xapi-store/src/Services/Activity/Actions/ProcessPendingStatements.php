<?php

namespace Trax\XapiStore\Services\Activity\Actions;

use Illuminate\Support\Collection;

trait ProcessPendingStatements
{
    use RecordStatementsActivities;
    
    /**
     * Process the pending statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    public function processPendingStatements(Collection $statements): void
    {
        $this->recordStatementsActivities($statements);
    }
}
