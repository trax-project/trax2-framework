<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Stores\Agents\AgentFactory;
use Trax\XapiStore\Relations\StatementAgent;

trait RecordStatementsAgents
{
    /**
     * Save the statements agents and return the agent models.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return \Illuminate\Support\Collection
     */
    protected function recordStatementsAgents(Collection $statements): Collection
    {
        // Collect agents info.
        $agentsInfo = $this->statementsAgentsInfo($statements);

        // Get existing agents.
        $existingAgents = $this->getExistingAgents($agentsInfo);

        // Insert the new agents.
        $newAgentsInfo = $this->getNewAgentsInfo($existingAgents, $agentsInfo);
        try {
            $newAgents = $this->insertAndGetAgents($newAgentsInfo);
        } catch (\Exception $e) {
            // We may have a concurrency issue when queues are not used.
            // We accept to loose some data here when 2 processes try to create the same agent.
            $this->recordStatementsRelations($existingAgents, $agentsInfo);
            return $existingAgents;
        }

        // Record statements relations.
        $agents = $existingAgents->concat($newAgents);
        $this->recordStatementsRelations($agents, $agentsInfo);

        return $agents;
    }

    /**
     * Extract agents from a list of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return array
     */
    protected function statementsAgentsInfo(Collection $statements): array
    {
        $agentsInfo = [];
        foreach ($statements as $statement) {
            // Main statement.
            $agentsInfo = array_merge($agentsInfo, $this->statementAgentsInfo($statement->id, $statement->data));
            // Sub-statement.
            if (isset($statement->data->object->objectType) && $statement->data->object->objectType == 'SubStatement') {
                $agentsInfo = array_merge($agentsInfo, $this->statementAgentsInfo($statement->id, $statement->data, true));
            }
        }
        return $agentsInfo;
    }

    /**
     * Extract agents from a statement.
     *
     * @param  integer  $statementId
     * @param  object  $statementData
     * @param  bool  $sub
     * @param  object  $authority
     * @return array
     */
    protected function statementAgentsInfo(int $statementId, object $statementData, bool $sub = false): array
    {
        $agentsInfo = [];

        // Actor agent.
        if (!isset($statementData->actor->objectType) || $statementData->actor->objectType == 'Agent') {
            $agentsInfo[] = $this->agentInfo(
                $statementId,
                $statementData->actor,
                StatementAgent::TYPE_ACTOR,
                $sub
            );
        }
        // Actor group.
        if (isset($statementData->actor->objectType) && $statementData->actor->objectType == 'Group') {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo(
                $statementId,
                $statementData->actor,
                StatementAgent::TYPE_ACTOR,
                $sub
            ));
        }
        // Object agent.
        if (isset($statementData->object->objectType) && $statementData->object->objectType == 'Agent') {
            $agentsInfo[] = $this->agentInfo(
                $statementId,
                $statementData->object,
                StatementAgent::TYPE_OBJECT,
                $sub
            );
        }
        // Object group.
        if (isset($statementData->object->objectType) && $statementData->object->objectType == 'Group') {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo(
                $statementId,
                $statementData->object,
                StatementAgent::TYPE_OBJECT,
                $sub
            ));
        }
        // Instructor agent.
        if (isset($statementData->context->instructor)
            && (!isset($statementData->context->instructor->objectType) || $statementData->context->instructor->objectType == "Agent")
        ) {
            $agentsInfo[] = $this->agentInfo(
                $statementId,
                $statementData->context->instructor,
                StatementAgent::TYPE_INSTRUCTOR,
                $sub
            );
        }
        // Instructor group.
        if (isset($statementData->context->instructor)
            && isset($statementData->context->instructor->objectType)
            && $statementData->context->instructor->objectType == "Group"
        ) {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo(
                $statementId,
                $statementData->context->instructor,
                StatementAgent::TYPE_INSTRUCTOR,
                $sub
            ));
        }
        // Team (always group).
        if (isset($statementData->context->team)) {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo(
                $statementId,
                $statementData->context->team,
                StatementAgent::TYPE_TEAM,
                $sub
            ));
        }
        // Authority agent.
        if (isset($statementData->authority)
            && (!isset($statementData->authority->objectType) || $statementData->authority->objectType == "Agent")
        ) {
            $agentsInfo[] = $this->agentInfo(
                $statementId,
                $statementData->authority,
                StatementAgent::TYPE_AUTHORITY,
                $sub
            );
        }
        // Authority group.
        if (isset($statementData->authority)
            && isset($statementData->authority->objectType)
            && $statementData->authority->objectType == "Group"
        ) {
            $agentsInfo = array_merge($agentsInfo, $this->groupAgentsInfo(
                $statementId,
                $statementData->authority,
                StatementAgent::TYPE_AUTHORITY,
                $sub
            ));
        }
        return $agentsInfo;
    }

    /**
     * Extract agents from a group.
     *
     * @param  integer  $statementId
     * @param  object  $group
     * @param  string  $type
     * @param  bool  $sub
     * @return array
     */
    protected function groupAgentsInfo(int $statementId, object $group, string $type, bool $sub = false): array
    {
        $agentsInfo = [];

        // Add members.
        if (isset($group->member)) {
            foreach ($group->member as $member) {
                $agentsInfo[] = $this->agentInfo($statementId, $member, $type, $sub);
            }
        }

        // Add identified groups.
        if (isset($group->mbox) || isset($group->mbox_sha1sum) || isset($group->openid) || isset($group->account)) {
            $identifiedGroup = clone $group;
            unset($identifiedGroup->member);
            $agentInfo = $this->agentInfo($statementId, $identifiedGroup, $type, $sub);
            $agentInfo->group = true;
            $agentsInfo[] = $agentInfo;
        }
        return $agentsInfo;
    }

    /**
     * Create an agent info object.
     *
     * @param  integer  $statementId
     * @param  object  $agent
     * @param  string  $type
     * @param  bool  $sub
     * @return object
     */
    protected function agentInfo(int $statementId, object $agent, string $type, bool $sub = false): object
    {
        return (object)[
            'vid' => AgentFactory::virtualId($agent),
            'agent' => $agent,
            'type' => $type,
            'sub' => $sub,
            'group' => false,
            'statementId' => $statementId
        ];
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
        return $this->repository->whereVidIn($vids);
    }

    /**
     * Get the new agents info.
     *
     * @param  \Illuminate\Support\Collection  $existingAgents
     * @param  array  $agentsInfo
     * @return array
     */
    protected function getNewAgentsInfo(Collection $existingAgents, array $agentsInfo): array
    {
        return array_filter($agentsInfo, function ($agentInfo) use ($existingAgents) {
            return $existingAgents->search(function ($agent) use ($agentInfo) {
                return $agent->vid == $agentInfo->vid;
            }) === false;
        });
    }

    /**
     * Insert new agents.
     *
     * @param  \Illuminate\Support\Collection  $existingAgents
     * @param  array  $agentsInfo
     * @return \Illuminate\Support\Collection
     */
    protected function insertAndGetAgents(array $agentsInfo): Collection
    {
        // Unique agents.
        $uniqueAgentsInfo = collect($agentsInfo)->keyBy('vid')->values();

        // Insert persons.
        $batch = $uniqueAgentsInfo->map(function ($agentInfo) {
            return $this->newPersonData();
        })->all();
        $persons = app(\Trax\XapiStore\Stores\Persons\PersonRepository::class)->insertAndGet($batch);

        // Insert pseudonymized agents.
        $pseudos = collect([]);
        if (config('trax-xapi-store.privacy.pseudonymization', false)) {
            $batch = $uniqueAgentsInfo->map(function ($agentInfo) use ($persons) {
                $objectType = isset($agentInfo->agent->objectType) ? $agentInfo->agent->objectType : 'Agent';
                return $this->newPseudoData($objectType, $persons->pop()->id);
            })->all();
            $pseudos = $this->repository->insertAndGet($batch);
        }

        // Insert native agents.
        $batch = $uniqueAgentsInfo->map(function ($agentInfo) use ($persons, $pseudos) {
            if (config('trax-xapi-store.privacy.pseudonymization', false)) {
                $pseudo = $pseudos->pop();
                return $this->newAgentData($agentInfo->agent, $pseudo->person_id, $pseudo->id);
            } else {
                $person = $persons->pop();
                return $this->newAgentData($agentInfo->agent, $person->id, null);
            }
        })->all();

        return $this->repository->insertAndGet($batch);
    }

    /**
     * Get data for a new person.
     *
     * @return array
     */
    protected function newPersonData(): array
    {
        return ['owner_id' => TraxAuth::context('owner_id')];
    }

    /**
     * Get data for a new pseudo.
     *
     * @param  string  $objectType
     * @param  integer  $personId
     * @return array
     */
    protected function newPseudoData(string $objectType, $personId): array
    {
        return [
            'agent' => [
                'objectType' => $objectType,
                'account' => [
                    'name' => \Str::uuid(),
                    'homePage' => config('trax-xapi-store.privacy.pseudo_iri', 'http://pseudo.traxlrs.com'),
                ]
            ],
            'person_id' => $personId,
            'owner_id' => TraxAuth::context('owner_id'),
            'pseudonymized' => true
        ];
    }

    /**
     * Get data for a new agent.
     *
     * @param  object  $agent
     * @param  integer  $personId
     * @param  integer|null  $pseudoId
     * @return array
     */
    protected function newAgentData(object $agent, $personId, $pseudoId = null): array
    {
        return [
            'agent' => $agent,
            'person_id' => $personId,
            'pseudo_id' => $pseudoId,
            'owner_id' => TraxAuth::context('owner_id')
        ];
    }

    /**
     * Record statements relations.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @param  array  $agentsInfo
     * @return void
     */
    protected function recordStatementsRelations(Collection $agents, array $agentsInfo): void
    {
        $relations = collect($agentsInfo)->map(function ($info) use ($agents) {
            if (!$agent = $agents->where('vid', $info->vid)->first()) {
                return false;
            }
            return [
                'agent_id' => $agent->id,
                'statement_id' => $info->statementId,
                'type' => intval($info->type),
                'sub' => $info->sub,
                'group' => $info->group,
            ];
        });
        $this->repository->insertStatementsRelations($relations->filter()->all());
    }
}
