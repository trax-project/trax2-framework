<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Trax\Auth\TraxAuth;
use Illuminate\Support\Collection;
use Trax\XapiValidation\Statement;
use Trax\XapiStore\Events\StatementRecordsInserted;

trait RecordStatements
{
    /**
     * Record the pending statements.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @param  bool  $validated
     * @return array
     */
    protected function recordPendingStatements(array $statements, object $authority, bool $validated = false): array
    {
        return $this->recordStatements($statements, $authority, $validated, true);
    }

    /**
     * Record the statements.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @param  bool  $validated
     * @param  bool  $pending
     * @return array
     */
    protected function recordStatements(array $statements, object $authority, bool $validated = false, bool $pending = false): array
    {
        $batch = $this->statementsBatch($statements, $authority, $validated, $pending);
        try {
            $this->repository->insert($batch->toArray());
        } catch (\Illuminate\Database\QueryException $e) {
            // This may be a unicity issue on statements ID which has not been validated (import or disabled
            // validation), or a concurrency issue (after validation, 2 requests insert statements with same ids).
            // So we try to validate IDs again.
            Statement::validateStatementIds($statements);
            // No error, so it was not a unicity issue. We throw the exception.
            throw $e;
        }
        StatementRecordsInserted::dispatch($batch);
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
    protected function statementsBatch(array $statements, object $authority, bool $validated = false, bool $pending = false): Collection
    {
        return collect($statements)->map(function ($statement) use ($authority, $validated, $pending) {

            // Be sure to work on objects.
            if (is_array($statement)) {
                $statement = json_decode(json_encode($statement));
            }
            
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
                'validation' => $validated ? 1 : 0,
                'pending' => $pending,
            ], TraxAuth::context());
        });
    }
}
