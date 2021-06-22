<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\XapiStore\XapiDate;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\XapiAgentFilter;

trait XapiStatementFilters
{
    use XapiAgentFilter;
    
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'statementId',
            'voidedStatementId',
            'agent',
            'verb',
            'activity',
            'registration',
            'related_activities',
            'related_agents',
            'since',
            'until',
        ];
    }

    /**
     * Filter: statementId.
     *
     * @param  string  $id
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function statementIdFilter($id, Query $query = null)
    {
        return [
            ['voided' => false],
            ['uuid' => $id],
        ];
    }

    /**
     * Filter: voidedStatementId.
     *
     * @param  string  $id
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function voidedStatementIdFilter($id, Query $query = null)
    {
        return [
            ['voided' => true],
            ['uuid' => $id],
        ];
    }

    /**
     * Filter: agent.
     *
     * @param  string|array|object  $agent
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function agentFilter($agent, Query $query = null)
    {
        if (is_string($agent)) {
            $agent = json_decode($agent, true);
        } elseif (is_object($agent)) {
            $agent = json_decode(json_encode($agent), true);
        }

        // Simple.
        if (is_null($query) || !$query->hasOption('related_agents') || $query->option('related_agents') == 'false') {
            return ['$or' => [
                $this->agentFilterConditions('data->actor', $agent),
                $this->agentFilterConditions('data->object', $agent),

                // Not clear if we should look into the members.
                // Seems to be the case in the spec, not in the test suite.
                //$this->agentFilterConditions('data->actor->member[*]', $agent),
                //$this->agentFilterConditions('data->object->member[*]', $agent),
            ]];
        }
        // Related.
        return ['$or' => [

            // Statement.
            $this->agentFilterConditions('data->actor', $agent),
            $this->agentFilterConditions('data->actor->member[*]', $agent),
            $this->agentFilterConditions('data->object', $agent),
            $this->agentFilterConditions('data->object->member[*]', $agent),
            $this->agentFilterConditions('data->context->instructor', $agent),
            $this->agentFilterConditions('data->context->instructor->member[*]', $agent),
            $this->agentFilterConditions('data->context->team', $agent),
            $this->agentFilterConditions('data->context->team->member[*]', $agent),

            // Sub-statement.
            $this->agentFilterConditions('data->object->actor', $agent),
            $this->agentFilterConditions('data->object->actor->member[*]', $agent),
            $this->agentFilterConditions('data->object->object', $agent),
            $this->agentFilterConditions('data->object->object->member[*]', $agent),
            $this->agentFilterConditions('data->object->context->instructor', $agent),
            $this->agentFilterConditions('data->object->context->instructor->member[*]', $agent),
            $this->agentFilterConditions('data->object->context->team', $agent),
            $this->agentFilterConditions('data->object->context->team->member[*]', $agent),

            // Authority.
            $this->agentFilterConditions('data->authority', $agent),
            $this->agentFilterConditions('data->authority->member[*]', $agent),
        ]];
    }

    /**
     * Filter: verb.
     *
     * @param  string  $id
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function verbFilter($id, Query $query = null)
    {
        return [
            ['data->verb->id' => $id],
        ];
    }

    /**
     * Filter: activity.
     *
     * @param  string  $id
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function activityFilter($id, Query $query = null)
    {
        // Simple.
        if (is_null($query) || !$query->hasOption('related_activities') || $query->option('related_activities') == 'false') {
            return [
                ['data->object->id' => $id],
            ];
        }
        // Related.
        return ['$or' => [

            // Main statement.
            ['data->object->id' => $id],
            ['data->context->contextActivities->parent[*]->id' => $id],
            ['data->context->contextActivities->grouping[*]->id' => $id],
            ['data->context->contextActivities->category[*]->id' => $id],
            ['data->context->contextActivities->other[*]->id' => $id],

            // Sub-statement.
            ['data->object->object->id' => $id],
            ['data->object->context->contextActivities->parent[*]->id' => $id],
            ['data->object->context->contextActivities->grouping[*]->id' => $id],
            ['data->object->context->contextActivities->category[*]->id' => $id],
            ['data->object->context->contextActivities->other[*]->id' => $id],
        ]];
    }

    /**
     * Filter: registration.
     *
     * @param  string  $registration
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function registrationFilter(string $registration, Query $query = null)
    {
        return [
            ['data->context->registration' => $registration],
        ];
    }

    /**
     * Filter: since.
     *
     * @param  string  $isoDate
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function sinceFilter(string $isoDate, Query $query = null)
    {
        return [
            ['data->stored' => ['$gt' => XapiDate::normalize($isoDate)]],
        ];
    }

    /**
     * Filter: until.
     *
     * @param  string  $isoDate
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function untilFilter(string $isoDate, Query $query = null)
    {
        return [
            ['data->stored' => ['$lte' => XapiDate::normalize($isoDate)]],
        ];
    }
}
