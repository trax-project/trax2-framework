<?php

namespace Trax\XapiStore\Services\Agent\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\Stores\Agents\AgentFactory;
use Trax\XapiStore\Stores\Statements\Statement;

trait PseudonymizeStatements
{
    /**
     * Pseudonymize a collection of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  \Illuminate\Support\Collection  $agents
     * @param  boolean  $allowPseudo
     * @return void
     */
    protected function pseudonymizeStatements(Collection $statements, Collection $agents, bool $allowPseudo): void
    {
        if (!$allowPseudo || !config('trax-xapi-store.privacy.pseudonymization', false)) {
            return;
        }
        $statements->transform(function ($statement) use ($agents) {
            return $this->pseudonymizeStatement($statement, $agents);
        });
    }

    /**
     * Pseudonymize a statement.
     *
     * @param  \Trax\XapiStore\Stores\Statements\Statement  $statement
     * @param  \Illuminate\Support\Collection  $agents
     * @return void
     */
    protected function pseudonymizeStatement(Statement $statement, Collection $agents): Statement
    {
        $data = $statement->data;

        // Actor agent.
        if (!isset($data->actor->objectType) || $data->actor->objectType == 'Agent') {
            $this->pseudonymizeAgent($data->actor, $agents);
        }
        // Actor group.
        if (isset($data->actor->objectType) && $data->actor->objectType == 'Group') {
            $this->pseudonymizeGroup($data->actor, $agents);
        }
        // Object agent.
        if (isset($data->object->objectType) && $data->object->objectType == 'Agent') {
            $this->pseudonymizeAgent($data->object, $agents);
        }
        // Object group.
        if (isset($data->object->objectType) && $data->object->objectType == 'Group') {
            $this->pseudonymizeGroup($data->object, $agents);
        }
        // Instructor agent.
        if (isset($data->context->instructor)
            && (!isset($data->context->instructor->objectType) || $data->context->instructor->objectType == "Agent")) {
                $this->pseudonymizeAgent($data->context->instructor, $agents);
        }
        // Instructor group.
        if (isset($data->context->instructor)
            && isset($data->context->instructor->objectType)
            && $data->context->instructor->objectType == "Group") {
                $this->pseudonymizeGroup($data->context->instructor, $agents);
        }
        // Team (always group).
        if (isset($data->context->team)) {
            $this->pseudonymizeGroup($data->context->team, $agents);
        }
        // Authority.
        // Don't pseudonymize the authority!!!

        $statement->data = $data;
        return $statement;
    }

    /**
     * Pseudonymize a group.
     *
     * @param  object  $group
     * @param  \Illuminate\Support\Collection  $agents
     * @return void
     */
    protected function pseudonymizeGroup(object $group, Collection $agents)
    {
        // Identified group.
        if (config('trax-xapi-store.privacy.pseudonymize_groups', true)) {
            if (!is_null(AgentFactory::virtualId($group))) {
                $this->pseudonymizeAgent($group, $agents);
            }
        }

        // Group members.
        if (isset($group->member)) {
            foreach ($group->member as &$member) {
                $this->pseudonymizeAgent($member, $agents);
            }
        }
    }

    /**
     * Pseudonymize an agent.
     *
     * @param  object  $agent
     * @param  \Illuminate\Support\Collection  $agents
     * @return void
     */
    protected function pseudonymizeAgent(object $agent, Collection $agents)
    {
        if (!$model = $agents->firstWhere('vid', AgentFactory::virtualId($agent))) {
            // This may happen when agents recording fail (e.g. concurrency issues)
            return;
        }

        if (is_null($model->pseudo)) {
            // This may happen when data were recorded before pseudonymization.
            return;
        }
        
        // Replace identifier by the pseudo account.
        unset($agent->name);
        unset($agent->mbox);
        unset($agent->mbox_sha1sum);
        unset($agent->openid);
        $agent->account = AgentFactory::reverseVirtualId($model->pseudo->vid, true)->account;
    }
}
