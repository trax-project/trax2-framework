<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\Stores\Verbs\Verb;

trait RecordVerbs
{
    /**
     * Save the statements verbs.
     *
     * @param  array  $statements
     * @param  array  $context
     * @return void
     */
    protected function recordStatementsVerbs(array $statements, array $context)
    {
        // Collect verbs info.
        $verbsInfo = $this->statementsVerbsInfo($statements);

        // Get existing verbs.
        $existingVerbs = $this->getExistingVerbs($verbsInfo, $context);

        // Insert the new verbs.
        try {
            $insertedBatch = $this->insertNewVerbs($existingVerbs, $verbsInfo, $context);
        } catch (\Exception $e) {
            // We may have a concurrency issue.
            // We accept to loose some data here!
            return;
        }

        // Index verbs.
        $this->indexVerbs($existingVerbs, $insertedBatch, $verbsInfo, $context);
    }

    /**
     * Get existing verbs.
     *
     * @param  array  $verbsInfo
     * @param  array  $context
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingVerbs(array $verbsInfo, array $context): Collection
    {
        $iris = collect($verbsInfo)->pluck('iri')->unique();
        return $this->verbs->addFilter([
            'iri' => ['$in' => $iris],
            'owner_id' => $context['owner_id']
        ])->get();
    }

    /**
     * Insert new verbs.
     *
     * @param  \Illuminate\Support\Collection  $existingVerbs
     * @param  array  $verbsInfo
     * @param  array  $context
     * @return array
     */
    protected function insertNewVerbs(Collection $existingVerbs, array $verbsInfo, array $context): array
    {
        // Get the new verbs.
        $newVerbsInfo = array_filter($verbsInfo, function ($verbInfo) use ($existingVerbs) {
            return $existingVerbs->search(function ($verb) use ($verbInfo) {
                return $verb->iri == $verbInfo->iri;
            }) === false;
        });

        // Prepare batch.
        $batch = collect($newVerbsInfo)->pluck('iri')->unique()->map(function ($iri) use ($context) {
            return [
                'iri' => $iri,
                'owner_id' => $context['owner_id']
            ];
        })->all();

        // Insert batch.
        return $this->verbs->insert($batch);
    }

    /**
     * Extract verbs from a list of statements.
     *
     * @param  array  $statements
     * @return array
     */
    protected function statementsVerbsInfo(array $statements): array
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
     * Index verbs.
     *
     * @param  \Illuminate\Support\Collection  $existingVerbs
     * @param  array  $insertedBatch
     * @param  array  $verbsInfo
     * @param  array  $context
     * @return void
     */
    protected function indexVerbs(Collection $existingVerbs, array $insertedBatch, array $verbsInfo, array $context): void
    {
        if (!config('trax-xapi-store.tables.verbs', false)
            || !config('trax-xapi-store.relations.statements_verbs', false)
        ) {
            return;
        }

        // Get back the new models.
        $iris = collect($insertedBatch)->pluck('iri')->toArray();
        $newVerbs = $this->verbs->addFilter([
            'owner_id' => $context['owner_id'],
            'iri' => ['$in' => $iris]
        ])->get();

        // Index them: new + existing!
        foreach ($verbsInfo as $verbInfo) {
            if ($newVerb = $newVerbs->where('iri', $verbInfo->iri)->first()) {
                $this->indexVerb($newVerb, $verbInfo);
            } else {
                $existingVerb = $existingVerbs->where('iri', $verbInfo->iri)->first();
                $this->indexVerb($existingVerb, $verbInfo);
            }
        }
    }

    /**
     * Index a verb.
     *
     * @param  \Trax\XapiStore\Stores\Verbs\Verb  $verb
     * @param  object  $verbInfo
     * @return void
     */
    protected function indexVerb(Verb $verb, object $verbInfo)
    {
        $verb->statements()->attach($verbInfo->statementId, [
            'sub' => $verbInfo->sub,
        ]);
    }
}
