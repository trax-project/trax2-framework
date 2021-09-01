<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;

trait RecordPendingStatements
{
    /**
     * Record the pending statements.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @return array
     */
    protected function recordPendingStatements(array $statements, object $authority): array
    {
        $batch = $this->pendingStatementsBatch($statements, $authority);
        $this->repository->insert($batch->toArray());
        return $batch->pluck('uuid')->all();
    }

    /**
     * Prepare the pending statements batch.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @return \Illuminate\Support\Collection
     */
    protected function pendingStatementsBatch(array $statements, object $authority): Collection
    {
        return collect($statements)->map(function ($statement) use ($authority) {

            // Set the authority.
            $statement->authority = $authority;

            // Set the ID now, we need to return it.
            if (!isset($statement->id)) {
                $statement->id = (string) \Str::uuid();
            }

            // Set pending to true, add the UUID and append the context.
            return array_merge(
                ['uuid' => $statement->id, 'data' => $statement, 'pending' => true],
                TraxAuth::context()
            );
        });
    }
}
