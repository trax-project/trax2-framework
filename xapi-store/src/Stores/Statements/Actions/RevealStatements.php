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
     * @return \Illuminate\Support\Collection
     */
    public function revealStatements(Collection $statements): Collection
    {
        // Nothing to do if pseudnonymization is not active.
        if (!config('trax-xapi-store.gdpr.pseudonymization', false)) {
            return $statements;
        }

        // First, get all the related agents.
        $agents = [];
        StatementAgent::whereIn('statement_id', $statements->pluck('id'))
            ->with(['agent', 'agent.pseudo'])
            ->each(function ($relation) use (&$agents) {
                if (!empty($relation->agent->pseudo_id)) {
                    $agents[AgentFactory::virtualId($relation->agent->pseudo->data)] = $relation->agent->data;
                }
            });

        // Then reveal the statements.
        return $statements->map(function ($model) use ($agents) {
            $data = $this->revealStatement($model->data, $agents);
            if (isset($data->object->objectType) && $data->object->objectType == 'SubStatement') {
                $data->object = $this->revealStatement($data->object, $agents);
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
     * @return object
     */
    protected function revealStatement(object $statement, array $agents): object
    {
        // Actor agent.
        if (!isset($statement->actor->objectType) || $statement->actor->objectType == 'Agent') {
            $this->revealAgent($statement->actor, $agents);
        }
        // Actor group.
        if (isset($statement->actor->objectType) && $statement->actor->objectType == 'Group') {
            $this->revealGroup($statement->actor, $agents);
        }
        // Object agent.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Agent') {
            $this->revealAgent($statement->object, $agents);
        }
        // Object group.
        if (isset($statement->object->objectType) && $statement->object->objectType == 'Group') {
            $this->revealGroup($statement->object, $agents);
        }
        // Instructor agent.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Agent") {
            $this->revealAgent($statement->context->instructor, $agents);
        }
        // Instructor group.
        if (isset($statement->context->instructor) && $statement->context->instructor->objectType == "Group") {
            $this->revealGroup($statement->context->instructor, $agents);
        }
        // Team (always group).
        if (isset($statement->context->team)) {
            $this->revealGroup($statement->context->team, $agents);
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
     * @return void
     */
    protected function revealGroup(object $group, array $agents)
    {
        // Identified group.
        $this->revealAgent($group, $agents);

        // Group members.
        if (isset($group->member)) {
            foreach ($group->member as &$member) {
                $this->revealAgent($member, $agents);
            }
        }
    }

    /**
     * Index a statement agent.
     *
     * @param  object  $agent
     * @param  array  $agents
     * @return void
     */
    protected function revealAgent(object $agent, array $agents)
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
        if (isset($agents[$vid]->name) && isset($agent->name)) {
            $agent->name = $agents[$vid]->name;
        }

        // Restore the identifier.
        unset($agent->account);
        if (isset($agents[$vid]->mbox)) {
            $agent->mbox = $agents[$vid]->mbox;
        }
        if (isset($agents[$vid]->mbox_sha1sum)) {
            $agent->mbox_sha1sum = $agents[$vid]->mbox_sha1sum;
        }
        if (isset($agents[$vid]->openid)) {
            $agent->openid = $agents[$vid]->openid;
        }
        if (isset($agents[$vid]->account)) {
            $agent->account = $agents[$vid]->account;
        }
    }
}
