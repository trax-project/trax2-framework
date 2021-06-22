<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Auth\TraxAuth;
use Trax\XapiStore\Stores\Statements\Statement;
use Trax\XapiStore\Stores\Agents\AgentFactory;

trait RecordStatements
{
    use PseudonymizeStatement;

    /**
     * Save the statements.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @param  array  $agentsInfo
     * @param  bool  $pseudonymize
     * @return void
     */
    protected function recordStatements(array $statements, object $authority, array $agentsInfo, bool $pseudonymize = true): array
    {
        // Record the statements batch.
        $batch = $this->statementsBatch($statements, $authority, $agentsInfo, $pseudonymize);
        $insertedBatch = $this->insert($batch);

        // Get back the models. We need them.
        // They are needed to index agents, activities and verbs.
        // We should remove this request when indexing will be moved in a Job.
        $uuids = collect($insertedBatch)->pluck('uuid')->toArray();
        $models = $this->addFilter([
            'owner_id' => TraxAuth::context('owner_id'),
            'uuid' => ['$in' => $uuids]
        ])->get()->all();
    
        // Index related agents.
        if (config('trax-xapi-store.tables.agents', false)
            && config('trax-xapi-store.relations.statements_agents', false)
        ) {
            $this->indexStatementsAgents($models, $statements, $agentsInfo);
        }
    
        return $models;
    }

    /**
     * Save the statements agents.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @param  array  $agentsInfo
     * @param  bool  $pseudonymize
     * @return array
     */
    protected function statementsBatch(array &$statements, object $authority, array $agentsInfo, bool $pseudonymize = true): array
    {
        $batch = [];
        foreach ($statements as &$statement) {

            // Set the authority.
            $statement->authority = $authority;

            // Set the ID now, we need it to keep the link between original and pseudonymized statements.
            if (!isset($statement->id)) {
                $statement->id = (string) \Str::uuid();
            }

            // Pseudonymize.
            $pseudonymized = $statement;
            if ($pseudonymize && config('trax-xapi-store.gdpr.pseudonymization', false)) {
                // Deep copy to preserve the original.
                $pseudonymized = json_decode(json_encode($statement));
                // Pseudonymize.
                $this->pseudonymizeStatement($pseudonymized, $agentsInfo);
                if (isset($pseudonymized->object->objectType) && $pseudonymized->object->objectType == 'SubStatement') {
                    $this->pseudonymizeStatement($pseudonymized->object, $agentsInfo);
                }
            }

            // Record the statement.
            $batch[] = array_merge(['data' => $pseudonymized], TraxAuth::context());
        }
        return $batch;
    }
    
    /**
     * Index the statements agents.
     *
     * @param  array  $models
     * @param  array  $statements
     * @param  array  $agentsInfo
     * @return void
     */
    protected function indexStatementsAgents(array $models, array $statements, array $agentsInfo)
    {
        $statements = collect($statements)->keyBy('id')->all();
        foreach ($models as $model) {
            $statement = $statements[$model->uuid];
            $this->indexStatementAgents($model, $statement, $agentsInfo, false);
            if (isset($statement->object->objectType) && $statement->object->objectType == 'SubStatement') {
                $this->indexStatementAgents($model, $statement->object, $agentsInfo, true);
            }
        }
    }

    /**
     * Index the statement agents.
     *
     * @param  \Trax\XapiStore\Stores\Statements\Statement  $statement
     * @param  object  $statement
     * @param  array  $agentsInfo
     * @param  bool  $sub
     * @return void
     */
    protected function indexStatementAgents(Statement $model, object $statement, array $agentsInfo, bool $sub = false)
    {
        // Actor agent.
        if (!isset($statement->actor->objectType) || $statement->actor->objectType == 'Agent') {
            $this->indexStatementAgent($model, $statement->actor, 'actor', $sub, $agentsInfo);
        }
        // Actor group.
        if (isset($statement->actor->objectType) && $statement->actor->objectType == 'Group') {
            $this->indexStatementGroup($model, $statement->actor, 'actor', $sub, $agentsInfo);
        }
        // Object agent.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Agent') {
            $this->indexStatementAgent($model, $statement->object, 'object', $sub, $agentsInfo);
        }
        // Object group.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Group') {
            $this->indexStatementGroup($model, $statement->object, 'object', $sub, $agentsInfo);
        }
        // Instructor agent.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Agent") {
            $this->indexStatementAgent($model, $statement->context->instructor, 'instructor', $sub, $agentsInfo);
        }
        // Instructor group.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Group") {
            $this->indexStatementGroup($model, $statement->context->instructor, 'instructor', $sub, $agentsInfo);
        }
        // Team (always group).
        if (isset($statement->context->team)) {
            $this->indexStatementGroup($model, $statement->context->team, 'team', $sub, $agentsInfo);
        }
        // Authority.
        if (!$sub) {
            $this->indexStatementAgent($model, $statement->authority, 'authority', $sub, $agentsInfo);
        }
    }

    /**
     * Index a statement group.
     *
     * @param  \Trax\XapiStore\Stores\Statements\Statement  $model
     * @param  object  $group
     * @param  string  $type
     * @param  bool  $sub
     * @param  array  $agentsInfo
     * @return void
     */
    protected function indexStatementGroup(Statement $model, object $group, string $type, bool $sub, array $agentsInfo)
    {
        // Identified group.
        $vid = AgentFactory::virtualId($group);

        if (!isset($agentsInfo[$vid])) {
            // This may happen when agents recording fail (e.g. concurrency issues)
            return;
        }

        if (!is_null($vid)) {
            $model->agents()->attach($agentsInfo[$vid]->model->id, [
                'type' => $type,
                'sub' => $sub,
                'group' => true,
            ]);
        }

        // Group members.
        if (isset($group->member)) {
            foreach ($group->member as $member) {
                $this->indexStatementAgent($model, $member, $type, $sub, $agentsInfo);
            }
        }
    }

    /**
     * Index a statement agent.
     *
     * @param  \Trax\XapiStore\Stores\Statements\Statement  $model
     * @param  object  $agent
     * @param  string  $type
     * @param  bool  $sub
     * @param  array  $agentsInfo
     * @return void
     */
    protected function indexStatementAgent(Statement $model, object $agent, string $type, bool $sub, array $agentsInfo)
    {
        $vid = AgentFactory::virtualId($agent);

        if (!isset($agentsInfo[$vid])) {
            // This may happen when agents recording fail (e.g. concurrency issues)
            return;
        }

        $model->agents()->attach($agentsInfo[$vid]->model->id, [
            'type' => $type,
            'sub' => $sub,
            'group' => false,
        ]);
    }
}
