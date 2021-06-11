<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Statements\Actions\RequestAgent;
use Trax\XapiStore\Stores\Statements\Actions\RequestVerb;
use Trax\XapiStore\Stores\Statements\Actions\RequestActivity;
use Trax\XapiStore\Stores\Statements\Actions\RequestMagicActor;
use Trax\XapiStore\Stores\Statements\Actions\RequestMagicVerb;
use Trax\XapiStore\Stores\Statements\Actions\RequestMagicObject;
use Trax\XapiStore\Stores\Statements\Actions\RequestMagicContext;

trait RequestStatement
{
    use RequestAgent, RequestVerb, RequestActivity,
        RequestMagicActor, RequestMagicVerb, RequestMagicObject, RequestMagicContext;

    /**
     * Statement filtering.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param  string|int  $ownerId
     * @param bool  $reveal
     * @return bool
     */
    protected function requestStatement(Query $query = null, $ownerId = null, bool $reveal = true): bool
    {
        // Request agent from API.
        if (!$match = $this->requestAgent($query, $ownerId)) {
            return false;
        }

        // Request verb from API.
        if (!$match = $this->requestVerb($query, $ownerId)) {
            return false;
        }

        // Request activity from API.
        if (!$match = $this->requestActivity($query, $ownerId)) {
            return false;
        }

        // Request actor from UI.
        if (!$match = $this->requestMagicActor($query, $ownerId, $reveal)) {
            return false;
        }

        // Request verb from UI.
        if (!$match = $this->requestMagicVerb($query, $ownerId)) {
            return false;
        }

        // Request object from UI.
        if (!$match = $this->requestMagicObject($query, $ownerId, $reveal)) {
            return false;
        }

        // Request context from UI.
        if (!$match = $this->requestMagicContext($query, $ownerId, $reveal)) {
            return false;
        }

        return true;
    }
}
