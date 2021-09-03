<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;

trait RecordStatements
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
        return $this->recordStatements($statements, $authority, true);
    }

    /**
     * Record the statements.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @param  bool  $pending
     * @return array
     */
    protected function recordStatements(array $statements, object $authority, bool $pending = false): array
    {
        $batch = $this->statementsBatch($statements, $authority, $pending);
        $this->repository->insert($batch->toArray());
        return $batch->pluck('uuid')->all();
    }

    /**
     * Prepare the pending statements batch.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @param  bool  $pending
     * @return \Illuminate\Support\Collection
     */
    protected function statementsBatch(array $statements, object $authority, bool $pending = false): Collection
    {
        return collect($statements)->map(function ($statement) use ($authority, $pending) {

            // Set the authority.
            $statement->authority = $authority;

            // Set the ID now, we may need it to get back inserted statements.
            if (!isset($statement->id)) {
                $statement->id = (string) \Str::uuid();
            }

            // Set data.
            return array_merge([
                'uuid' => $statement->id,
                'data' => $statement,
                'pending' => $pending,
                'validated' => !config('trax-xapi-store.processing.disable_validation', false)
            ], TraxAuth::context());
        });
    }
}
