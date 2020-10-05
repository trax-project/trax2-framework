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
            'magicActor',
            'magicVerb',
            'magicObject',
            'magicContext',
        ]);
    }

    /**
     * Filter: magicActor.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function magicActorFilter($field, Query $query)
    {
        return $this->getMagicAgentFilter($field, 'data->actor');
    }

    /**
     * Filter: magicVerb.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function magicVerbFilter($field, Query $query)
    {
        return $this->getMagicVerbFilter($field, 'data->verb');
    }

    /**
     * Filter: magicObject.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function magicObjectFilter($field, Query $query)
    {
        // Agent.
        $filter = $this->getMagicAgentFilter($field, 'data->object');
        if (!empty($filter)) {
            return $filter;
        }

        // Activity.
        return $this->getMagicActivityFilter($field, 'data->object');
    }

    /**
     * Does context filter support relational request?
     *
     * @param  string  $field
     * @param  string  $target
     * @param  bool  $fulltext
     * @return bool
     */
    protected function relationalMagicContext($field)
    {
        return !empty($this->getMagicContextAgentFilter($field))
        || !empty($this->getMagicContextActivityFilter($field, 'parent'))
        || !empty($this->getMagicContextActivityFilter($field, 'grouping'))
        || !empty($this->getMagicContextActivityFilter($field, 'category'))
        // No relational search by profile currently. The cost is too hight. We should index profiles.
        || empty($this->getMagicContextProfileFilter($field))
        ;
    }

    /**
     * Filter: magicContext.
     *
     * @param  string  $field
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function magicContextFilter($field, Query $query)
    {
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

        // profile.
        $filter = $this->getMagicContextProfileFilter($field);
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
     * Get context agent filter.
     *
     * @param  string  $field
     * @return array
     */
    protected function getMagicContextAgentFilter($field)
    {
        // Account filter.
        if (\Str::startsWith($field, 'account:')) {
            $field = \Str::after($field, 'account:');
            if (empty($field)) {
                return [];
            }
            $parts = explode('@', $field);
            if (count($parts) == 1) {
                return [
                    ['$or' => [
                        'data->context->instructor->account->name' => $parts[0],
                        'data->context->team->account->name' => $parts[0],
                    ]],
                ];
            } else {
                return [
                    ['$or' => [
                        ['$and' => [
                            'data->context->instructor->account->name' => $parts[0],
                            'data->context->instructor->account->homePage' => $parts[1],
                        ]],
                        ['$and' => [
                            'data->context->team->account->name' => $parts[0],
                            'data->context->team->account->homePage' => $parts[1],
                        ]],
                    ]],
                ];
            }
        }

        // Mbox filter.
        $parts = explode('@', $field);
        if (count($parts) > 1) {
            return [
                ['$or' => [
                    'data->context->instructor->mbox' => 'mailto:' . $parts[0],
                    'data->context->team->mbox' => 'mailto:' . $parts[0],
                ]],
            ];
        }

        return [];
    }

    /**
     * Get context agent filter.
     *
     * @param  string  $field
     * @param  string  $relation
     * @return array
     */
    protected function getMagicContextActivityFilter($field, $relation)
    {
        if (\Str::startsWith($field, $relation.':')) {
            $field = \Str::after($field, $relation.':');
            if (empty($field)) {
                return [];
            }
            return [
                ['data->context->contextActivities->'.$relation.'[*]->id' => $field],
            ];
        }
    }

    /**
     * Get profile filter.
     *
     * @param  string  $field
     * @return array
     */
    protected function getMagicContextProfileFilter($field)
    {
        if (\Str::startsWith($field, 'profile:')) {
            $field = \Str::after($field, 'profile:');
            if (empty($field)) {
                return [];
            }
            return [
                ['data->context->contextActivities->category[*]->id' => ['$text' => $field]],
            ];
        }
    }
}
