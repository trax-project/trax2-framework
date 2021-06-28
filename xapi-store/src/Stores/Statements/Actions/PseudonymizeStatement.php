<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\XapiStore\Stores\Agents\AgentFactory;

trait PseudonymizeStatement
{
    /**
     * Pseudonymize a statement.
     *
     * @param  object  $statement
     * @param  array  $agentsInfo
     * @return void
     */
    protected function pseudonymizeStatement(object $statement, array $agentsInfo): void
    {
        // Actor agent.
        if (!isset($statement->actor->objectType) || $statement->actor->objectType == 'Agent') {
            $this->pseudonymizeAgent($statement->actor, $agentsInfo);
        }
        // Actor group.
        if (isset($statement->actor->objectType) && $statement->actor->objectType == 'Group') {
            $this->pseudonymizeGroup($statement->actor, $agentsInfo);
        }
        // Object agent.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Agent') {
            $this->pseudonymizeAgent($statement->object, $agentsInfo);
        }
        // Object group.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Group') {
            $this->pseudonymizeGroup($statement->object, $agentsInfo);
        }
        // Instructor agent.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Agent") {
            $this->pseudonymizeAgent($statement->context->instructor, $agentsInfo);
        }
        // Instructor group.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Group") {
            $this->pseudonymizeGroup($statement->context->instructor, $agentsInfo);
        }
        // Team (always group).
        if (isset($statement->context->team)) {
            $this->pseudonymizeGroup($statement->context->team, $agentsInfo);
        }
        // Authority (always agent).
        // Don't pseudonymize the authority!!!
    }

    /**
     * Index a statement group.
     *
     * @param  object  $group
     * @param  array  $agentsInfo
     * @return void
     */
    protected function pseudonymizeGroup(object $group, array $agentsInfo)
    {
        // Identified group.
        if (config('trax-xapi-store.gdpr.pseudonymize_groups', true)) {
            $vid = AgentFactory::virtualId($group);
            if (!is_null($vid)) {
                $this->pseudonymizeAgent($group, $agentsInfo);
            }
        }

        // Group members.
        if (isset($group->member)) {
            foreach ($group->member as &$member) {
                $this->pseudonymizeAgent($member, $agentsInfo);
            }
        }
    }

    /**
     * Index a statement agent.
     *
     * @param  object  $agent
     * @param  array  $agentsInfo
     * @return void
     */
    protected function pseudonymizeAgent(object $agent, array $agentsInfo)
    {
        $vid = AgentFactory::virtualId($agent);

        if (!isset($agentsInfo[$vid])) {
            // This may happen when agents recording fail (e.g. concurrency issues)
            return;
        }
        
        $pseudo = $agentsInfo[$vid]->model->pseudo;

        // Keep the name if present, but replace it.
        if (isset($agent->name)) {
            $agent->name = $pseudo->name;
        }

        // Replace identifier by the pseudo account.
        unset($agent->mbox);
        unset($agent->mbox_sha1sum);
        unset($agent->openid);
        $agent->account = AgentFactory::reverseVirtualId($pseudo->vid, true)->account;
    }
}
