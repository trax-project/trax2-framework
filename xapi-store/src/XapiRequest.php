<?php

namespace Trax\XapiStore;

use Trax\Repo\CrudRequest;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\Traits\AcceptAlternateRequests;
use Trax\Auth\TraxAuth;

class XapiRequest extends CrudRequest
{
    use AcceptAlternateRequests;

    /**
     * Get data to be recorded.
     *
     * @return array
     */
    public function data(): array
    {
        return ['data' => $this->content()];
    }

    /**
     * Get the matching query.
     *
     * @return \Trax\Repo\Querying\Query
     */
    public function query(): Query
    {
        // Query data.
        $query = [];

        // Params: we don't use directly $this->params because we don't want to change it.
        $params = $this->params;

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

    /**
     * Validate the request against contextual rules.
     *
     * @return \Trax\XapiStore\XapiRequest
     */
    public function validate(bool $flag = false): XapiRequest
    {
        if (TraxAuth::isUser()) {
            return $this;
        }
        if (!isset(TraxAuth::access()->meta['validation_class'])) {
            return $this;
        }
        $class = TraxAuth::access()->meta['validation_class'];
        (new $class)->validate($this);
        return $this;
    }
}
