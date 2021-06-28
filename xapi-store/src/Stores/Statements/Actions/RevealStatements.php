<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Illuminate\Support\Collection;
use Trax\XapiStore\Stores\Agents\AgentFactory;
use Trax\XapiStore\Relations\StatementAgent;

trait RevealStatements
{
    /**
     * Get resources and de-pseudonymize.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  bool  $removeNames
     * @return \Illuminate\Support\Collection
     */
    public function revealStatements(Collection $statements, bool $removeNames = false): Collection
    {
        // Nothing to do if pseudnonymization is not active.
        if (!config('trax-xapi-store.gdpr.pseudonymization', false)) {
            return $statements;
        }

        // First, get all the related agents.
        $agents = [];
        StatementAgent::whereIn('statement_id', $statements->pluck('id'))
            ->with(['agent', 'agent.pseudo'])
            // Force the order because Eloquent would set it to 'id' prop, which does not exist.
            ->orderBy('statement_id')
            ->each(function ($relation) use (&$agents) {
                if (!empty($relation->agent->pseudo_id)) {
                    $agents[$relation->agent->pseudo->vid] = $relation->agent;
                }
            });

        // Then reveal the statements.
        return $statements->map(function ($model) use ($agents, $removeNames) {
            $data = $this->revealStatement($model->data, $agents, $removeNames);
            if (isset($data->object->objectType) && $data->object->objectType == 'SubStatement') {
                $data->object = $this->revealStatement($data->object, $agents, $removeNames);
            }
            $model->data = $data;
            return $model;
        });
    }

    /**
     * Pseudonymize a statement.
     *
     * @param  object  $statement
     * @param  array  $agents
     * @param  bool  $removeNames
     * @return object
     */
    protected function revealStatement(object $statement, array $agents, bool $removeNames = false): object
    {
        // Actor agent.
        if (!isset($statement->actor->objectType) || $statement->actor->objectType == 'Agent') {
            $this->revealAgent($statement->actor, $agents, $removeNames);
        }
        // Actor group.
        if (isset($statement->actor->objectType) && $statement->actor->objectType == 'Group') {
            $this->revealGroup($statement->actor, $agents, $removeNames);
        }
        // Object agent.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Agent') {
            $this->revealAgent($statement->object, $agents, $removeNames);
        }
        // Object group.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Group') {
            $this->revealGroup($statement->object, $agents, $removeNames);
        }
        // Instructor agent.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Agent") {
            $this->revealAgent($statement->context->instructor, $agents, $removeNames);
        }
        // Instructor group.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Group") {
            $this->revealGroup($statement->context->instructor, $agents, $removeNames);
        }
        // Team (always group).
        if (isset($statement->context->team)) {
            $this->revealGroup($statement->context->team, $agents, $removeNames);
        }
        // Authority (always agent).
        // Don't have to reveal the authority!!!
        return $statement;
    }

    /**
     * Index a statement group.
     *
     * @param  object  $group
     * @param  array  $agents
     * @param  bool  $removeNames
     * @return void
     */
    protected function revealGroup(object $group, array $agents, bool $removeNames = false)
    {
        // Identified group.
        $this->revealAgent($group, $agents, $removeNames);

        // Group members.
        if (isset($group->member)) {
            foreach ($group->member as &$member) {
                $this->revealAgent($member, $agents, $removeNames);
            }
        }
    }

    /**
     * Index a statement agent.
     *
     * @param  object  $agent
     * @param  array  $agents
     * @param  bool  $removeNames
     * @return void
     */
    protected function revealAgent(object $agent, array $agents, bool $removeNames = false)
    {
        $vid = AgentFactory::virtualId($agent);

        // There is no pseudo for this agent.
        if (!isset($agents[$vid])) {
            return;
        }

        // Restore the name if needed.
        // This is not perfect because the name of the agent can be lost.
        // It may be multiple statements writes with and without the agent name.
        // Only the first agent write is taken, hopefully with a name.
        if (!$removeNames && isset($agents[$vid]->name)) {
            $agent->name = $agents[$vid]->name;
        }

        // Restore the identifier.
        $revealed = AgentFactory::reverseVirtualId($agents[$vid]->vid, true);
        unset($agent->account);
        if (isset($revealed->mbox)) {
            $agent->mbox = $revealed->mbox;
        }
        if (isset($revealed->mbox_sha1sum)) {
            $agent->mbox_sha1sum = $revealed->mbox_sha1sum;
        }
        if (isset($revealed->openid)) {
            $agent->openid = $revealed->openid;
        }
        if (isset($revealed->account)) {
            $agent->account = $revealed->account;
        }
    }
}
