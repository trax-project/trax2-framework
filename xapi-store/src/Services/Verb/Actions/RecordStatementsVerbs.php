<?php

namespace Trax\XapiStore\Services\Verb\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;

trait RecordStatementsVerbs
{
    /**
     * Save the statements verbs.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    protected function recordStatementsVerbs(Collection $statements)
    {
        // Collect verbs info.
        $verbsInfo = $this->statementsVerbsInfo($statements);

        // Get existing verbs.
        $existingVerbs = $this->getExistingVerbs($verbsInfo);

        // Insert the new verbs.
        $newVerbsInfo = $this->getNewVerbsInfo($existingVerbs, $verbsInfo);
        try {
            $newVerbs = $this->insertAndGetVerbs($newVerbsInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue when queues are not used.
            // We accept to loose some data here when 2 processes try to create the same verb.
            $this->recordStatementsRelations($existingVerbs, $verbsInfo);
            return;
        }

        // Record statements relations.
        $this->recordStatementsRelations($existingVerbs->concat($newVerbs), $verbsInfo);
    }

    /**
     * Extract verbs from a collection of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return array
     */
    protected function statementsVerbsInfo(Collection $statements): array
    {
        $verbsInfo = [];
        foreach ($statements as $statement) {
            // Main statement.
            $verbsInfo[] = $this->statementVerbInfo($statement->id, $statement->data);
            // Sub-statement.
            if (isset($statement->data->object->objectType) && $statement->data->object->objectType == 'SubStatement') {
                $verbsInfo[] = $this->statementVerbInfo($statement->id, $statement->data->object, true);
            }
        }
        return $verbsInfo;
    }

    /**
     * Extract verb from a statement.
     *
     * @param  integer  $statementId
     * @param  object  $statementData
     * @param  bool  $sub
     * @return object
     */
    protected function statementVerbInfo(int $statementId, object $statementData, bool $sub = false): object
    {
        return (object)[
            'iri' => $statementData->verb->id,
            'sub' => $sub,
            'statementId' => $statementId
        ];
    }

    /**
     * Get existing verbs.
     *
     * @param  array  $verbsInfo
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingVerbs(array $verbsInfo): Collection
    {
        $iris = collect($verbsInfo)->pluck('iri')->unique()->toArray();
        return $this->repository->whereIriIn($iris);
    }

    /**
     * Get the new verbs info.
     *
     * @param  \Illuminate\Support\Collection  $existingVerbs
     * @param  array  $verbsInfo
     * @return array
     */
    protected function getNewVerbsInfo(Collection $existingVerbs, array $verbsInfo): array
    {
        return array_filter($verbsInfo, function ($verbInfo) use ($existingVerbs) {
            return $existingVerbs->search(function ($verb) use ($verbInfo) {
                return $verb->iri == $verbInfo->iri;
            }) === false;
        });
    }

    /**
     * Insert verbs.
     *
     * @param  array  $verbsInfo
     * @return \Illuminate\Support\Collection
     */
    protected function insertAndGetVerbs(array $verbsInfo): Collection
    {
        $batch = collect($verbsInfo)->pluck('iri')->unique()->map(function ($iri) {
            return [
                'iri' => $iri,
                'owner_id' => TraxAuth::context('owner_id')
            ];
        })->all();

        return $this->repository->insertAndGet($batch);
    }

    /**
     * Record statements relations.
     *
     * @param  \Illuminate\Support\Collection  $verbs
     * @param  array  $verbsInfo
     * @return void
     */
    protected function recordStatementsRelations(Collection $verbs, array $verbsInfo): void
    {
        $relations = collect($verbsInfo)->map(function ($info) use ($verbs) {
            if (!$verb = $verbs->where('iri', $info->iri)->first()) {
                return false;
            }
            return [
                'verb_id' => $verb->id,
                'statement_id' => $info->statementId,
                'sub' => $info->sub,
            ];
        });
        $this->repository->insertStatementsRelations($relations->filter()->all());
    }
}
