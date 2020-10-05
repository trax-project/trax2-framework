<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\XapiStore\XapiRequest;
use Trax\Repo\Querying\Query;

class XapiStatementRequest extends XapiRequest
{
    /**
     * Attachments.
     *
     * @var array
     */
    protected $attachments;

    /**
     * Make a request.
     *
     * @param  array  $params
     * @param  \stdClass|array  $content
     * @param  array  $attachments
     * @return void
     */
    public function __construct(array $params, $statements = null, $attachments = [])
    {
        parent::__construct($params, $statements);
        $this->attachments = $attachments;
    }

    /**
     * Get statements.
     *
     * @return \stdClass|array
     */
    public function statements()
    {
        return $this->content();
    }

    /**
     * Get attachments.
     *
     * @return array
     */
    public function attachments()
    {
        return $this->attachments;
    }

    /**
     * Get the matching query.
     *
     * @return \Trax\Repo\Querying\Query
     */
    public function query(): Query
    {
        // Query data.
        $query = [
            'sort' => ['-id'],
            'options' => [],
        ];

        // Params: we don't use directly $this->params because we don't want to change it.
        $params = $this->params;

        // Option: lang.
        if (isset($params['lang'])) {
            $query['options']['lang'] = $params['lang'];
            unset($params['lang']);
        }

        // Option: related_activities.
        if (isset($params['related_activities'])) {
            $query['options']['related_activities'] = $params['related_activities'];
            unset($params['related_activities']);
        }

        // Option: related_agents.
        if (isset($params['related_agents'])) {
            $query['options']['related_agents'] = $params['related_agents'];
            unset($params['related_agents']);
        }

        // Limit.
        if (isset($params['limit'])) {
            $query['limit'] = intval($params['limit']);
            unset($params['limit']);
        }

        // Option: format.
        if (isset($params['format'])) {
            $query['options']['format'] = $params['format'];
            unset($params['format']);
        }

        // Option: attachments.
        if (isset($params['attachments'])) {
            $query['options']['attachments'] = $params['attachments'];
            unset($params['attachments']);
        }

        // Ascending.
        if (isset($params['ascending']) && $params['ascending'] == 'true') {
            $query['sort'] = ['id'];
        }
        unset($params['ascending']);

        // Pagination: after.
        if (isset($params['after'])) {
            $query['after'] = $params['after'];
            unset($params['after']);
        }

        // Pagination: before.
        if (isset($params['before'])) {
            $query['before'] = $params['before'];
            unset($params['before']);
        }

        // Remove alternate params.
        foreach ($this->alternateInputs as $input) {
            unset($params[$input]);
        }

        // Others are used as filters.
        $query['filters'] = collect($params)->map(function ($val, $prop) {
            return [$prop => $val];
        })->values()->all();

        return new Query($query);
    }
}
