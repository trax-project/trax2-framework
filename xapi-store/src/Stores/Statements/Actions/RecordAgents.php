<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\Stores\Agents\AgentFactory;

trait RecordAgents
{
    /**
     * Save the statements agents.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @return array
     */
    protected function recordAgents(array $statements, object $authority): array
    {
        // Collect agents info.
        $agentsInfo = $this->statementsAgentsInfo($statements, $authority);

        // Get existing agents.
        $existingAgents = $this->getExistingAgents($agentsInfo);

        // Insert the new agents.
        try {
            $insertedBatch = $this->insertNewAgents($existingAgents, $agentsInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue.
            // We accept to loose some data here!
            return [];
        }

        // Update agents info with models.
        $this->updateAgentsInfoWithModels($existingAgents, $insertedBatch, $agentsInfo);

        return $agentsInfo;
    }

    /**
     * Get existing agents.
     *
     * @param  array  $agentsInfo
     * @return \Illuminate\Support\Collection
     */
    protected function getExistingAgents(array $agentsInfo): Collection
    {
        $vids = collect($agentsInfo)->pluck('vid')->unique()->toArray();
        return $this->agents->whereVidIn($vids);
    }

    /**
     * Insert new agents.
     *
     * @param  \Illuminate\Support\Collection  $existingAgents
     * @param  array  $agentsInfo
     * @return array
     */
    protected function insertNewAgents(Collection $existingAgents, array $agentsInfo): array
    {
        // Get the new agents.
        $newAgentsInfo = array_filter($agentsInfo, function ($agentInfo) use ($existingAgents) {
            return $existingAgents->search(function ($agent) use ($agentInfo) {
                return $agent->vid == $agentInfo->vid;
            }) === false;
        });

        // Unique agents.
        $uniqueAgentsInfo = collect($newAgentsInfo)->keyBy('vid')->values();

        // Insert persons.
        $batch = $uniqueAgentsInfo->map(function ($agentInfo) {
            return $this->agents->newPersonData();
        })->all();
        $persons = $this->insertPersonsAndReturnModels($batch);

        // Insert pseudonymized agents.
        $pseudos = collect([]);
        if (config('trax-xapi-store.gdpr.pseudonymization', false)) {
            $batch = $uniqueAgentsInfo->map(function ($agentInfo) use ($persons) {
                $objectType = isset($agentInfo->agent->objectType) ? $agentInfo->agent->objectType : 'Agent';
                return $this->agents->newPseudoData($objectType, $persons->pop()->id);
            })->all();
            $pseudos = $this->insertAgentsAndReturnModels($batch);
        }

        // Insert native agents.
        $batch = $uniqueAgentsInfo->map(function ($agentInfo) use ($persons, $pseudos) {
            if (config('trax-xapi-store.gdpr.pseudonymization', false)) {
                $pseudo = $pseudos->pop();
                return $this->agents->newAgentData($agentInfo->agent, $pseudo->person_id, $pseudo->id);
            } else {
                $person = $persons->pop();
                return $this->agents->newAgentData($agentInfo->agent, $person->id, null);
            }
        })->all();
        $agents = $this->insertAgentsAndReturnModels($batch);
        return $agents->all();
    }

    /**
     * Insert persons and return the models.
     *
     * @param  array  $batch
     * @return \Illuminate\Support\Collection
     */
    protected function insertPersonsAndReturnModels(array $batch): Collection
    {
        $inserted = $this->persons->insert($batch);
        $uuids = collect($inserted)->pluck('uuid')->toArray();
        return $this->persons->whereUuidIn($uuids);
    }

    /**
     * Insert agents and return the models.
     *
     * @param  array  $batch
     * @return \Illuminate\Support\Collection
     */
    protected function insertAgentsAndReturnModels(array $batch): Collection
    {
        $inserted = $this->agents->insert($batch);
        $vids = collect($inserted)->pluck('vid')->toArray();
        return $this->agents->whereVidIn($vids);
    }

    /**
     * Update agents info with models.
     *
     * @param  \Illuminate\Support\Collection  $existingAgents
     * @param  array  $insertedBatch
     * @param  array  $agentsInfo
     * @return void
     */
    protected function updateAgentsInfoWithModels(Collection $existingAgents, array $insertedBatch, array &$agentsInfo): void
    {
        // Get back the new models.
        $vids = collect($insertedBatch)->pluck('vid')->toArray();
        $newAgents = $this->agents->whereVidIn($vids);
        $this->agents->cache($newAgents);

        // Index them.
        foreach ($agentsInfo as &$agentInfo) {
            if (!$agentInfo->model = $newAgents->where('vid', $agentInfo->vid)->first()) {
                $agentInfo->model = $existingAgents->where('vid', $agentInfo->vid)->first();
            }
        }
    }

    /**
     * Extract agents from a list of statements.
     *
     * @param  array  $statements
     * @param  object  $authority
     * @return array
     */
    protected function statementsAgentsInfo(array $statements, object $authority): array
    {
        $agentsInfo = [];
        foreach ($statements as $statement) {
            // Main statement.
            $agentsInfo = array_merge($agentsInfo, $this->statementAgentsInfo($statement));
            // Sub-statement.
            if (isset($statement->object->objectType) && $statement->object->objectType == 'SubStatement') {
                $agentsInfo = array_merge($agentsInfo, $this->statementAgentsInfo($statement->object, true));
            }
        }

        // Authority agent.
        if (!isset($authority->objectType) || $authority->objectType == 'Agent') {
            $agentInfo = $this->agentInfo($authority);
            $agentsInfo[$agentInfo->vid] = $agentInfo;
        }

        // Authority group.
        if (isset($authority->objectType) && $authority->objectType == 'Group') {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo($authority));
        }
        
        return $agentsInfo;
    }

    /**
     * Extract agents from a statement.
     *
     * @param  object  $statement
     * @param  bool  $sub
     * @param  object  $authority
     * @return array
     */
    protected function statementAgentsInfo(object $statement, bool $sub = false): array
    {
        $agentsInfo = [];

        // Actor agent.
        if (!isset($statement->actor->objectType) || $statement->actor->objectType == 'Agent') {
            $agentInfo = $this->agentInfo($statement->actor);
            $agentsInfo[$agentInfo->vid] = $agentInfo;
        }
        // Actor group.
        if (isset($statement->actor->objectType) && $statement->actor->objectType == 'Group') {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo($statement->actor));
        }
        // Object agent.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Agent') {
            $agentInfo = $this->agentInfo($statement->object);
            $agentsInfo[$agentInfo->vid] = $agentInfo;
        }
        // Object group.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Group') {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo($statement->object));
        }
        // Instructor agent.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Agent") {
            $agentInfo = $this->agentInfo($statement->context->instructor);
            $agentsInfo[$agentInfo->vid] = $agentInfo;
        }
        // Instructor group.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Group") {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo($statement->context->instructor));
        }
        // Team (always group).
        if (isset($statement->context->team)) {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo($statement->context->team));
        }
        return $agentsInfo;
    }

    /**
     * Extract agents from a group.
     *
     * @param  object  $group
     * @return array
     */
    protected function groupAgentsInfo(object $group): array
    {
        $agentsInfo = [];

        // Add members.
        if (isset($group->member)) {
            foreach ($group->member as $member) {
                $agentInfo = $this->agentInfo($member);
                $agentsInfo[$agentInfo->vid] = $agentInfo;
            }
        }

        // Add identified groups.
        if (isset($group->mbox) || isset($group->mbox_sha1sum) || isset($group->openid) || isset($group->account)) {
            $identifiedGroup = clone $group;
            unset($identifiedGroup->member);
            $agentInfo = $this->agentInfo($identifiedGroup);
            $agentsInfo[$agentInfo->vid] = $agentInfo;
        }
        return $agentsInfo;
    }

    /**
     * Create an agent info object.
     *
     * @param  object  $agent
     * @return object
     */
    protected function agentInfo(object $agent): object
    {
        return (object)[
            'vid' => AgentFactory::virtualId($agent),
            'agent' => $agent,
            // 'model' => will be set after agent recording
        ];
    }
}
