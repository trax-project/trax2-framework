<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\MagicFilters;

trait StatementFilters
{
    use MagicFilters;
    use XapiStatementFilters {
        dynamicFilters as xapiDynamicFilters;
    }
    
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return array_merge($this->xapiDynamicFilters(), [
            'uiActor',
            'uiVerb',
            'uiObject',
            'uiContext',
            'relatedAgents',
        ]);
    }

    /**
     * Filter: uiActor.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiActorFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }
        return $this->getMagicAgentFilter($field, 'data->actor');
    }

    /**
     * Filter: uiVerb.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiVerbFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }

        return $this->getMagicVerbFilter($field, 'data->verb');
    }

    /**
     * Filter: uiObject.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiObjectFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }

        if ($this->hasMagicAgentFilter($field)) {
            // Agent.
            return $this->getMagicAgentFilter($field, 'data->object');
        } else {
            // Activity.
            return $this->getMagicActivityFilter($field, 'data->object');
        }
    }

    /**
     * Filter: uiContext.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function uiContextFilter($field, Query $query = null)
    {
        // Check if null. This may happen when the UI field is empty.
        if (is_null($field)) {
            return [];
        }

        // Agent.
        $filter = $this->getMagicContextAgentFilter($field);
        if (!empty($filter)) {
            return $filter;
        }

        // Parent.
        $filter = $this->getMagicContextActivityFilter($field, 'parent');
        if (!empty($filter)) {
            return $filter;
        }

        // grouping.
        $filter = $this->getMagicContextActivityFilter($field, 'grouping');
        if (!empty($filter)) {
            return $filter;
        }

        // category.
        $filter = $this->getMagicContextActivityFilter($field, 'category');
        if (!empty($filter)) {
            return $filter;
        }

        // other.
        $filter = $this->getMagicContextActivityFilter($field, 'other');
        if (!empty($filter)) {
            return $filter;
        }

        // Search in all context activities.
        return [
            ['$or' => [
                'data->context->contextActivities->parent[*]->id' => $field,
                'data->context->contextActivities->grouping[*]->id' => $field,
                'data->context->contextActivities->category[*]->id' => $field,
                'data->context->contextActivities->other[*]->id' => $field,
            ]],
        ];
    }

    /**
     * Filter: relatedAgents.
     *
     * @param  array  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function relatedAgentsFilter($field, Query $query = null)
    {
        $conditions = [];
        foreach ($field as $agent) {
            $conditions = array_merge($conditions, $this->relatedAgentConditions($agent));
        }
        return ['$or' => $conditions];
    }
}
