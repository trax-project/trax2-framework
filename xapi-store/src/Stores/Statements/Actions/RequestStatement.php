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
        // Request agent.
        if (!$match = $this->requestAgent($query, $ownerId)) {
            return false;
        }

        // Request verb.
        if (!$match = $this->requestVerb($query, $ownerId)) {
            return false;
        }

        // Request activity.
        if (!$match = $this->requestActivity($query, $ownerId)) {
            return false;
        }

        // Request UI actor.
        if (!$match = $this->requestMagicActor($query, $ownerId, $reveal)) {
            return false;
        }

        // Request UI verb.
        if (!$match = $this->requestMagicVerb($query, $ownerId)) {
            return false;
        }

        // Request UI object.
        if (!$match = $this->requestMagicObject($query, $ownerId, $reveal)) {
            return false;
        }

        // Request UI context.
        if (!$match = $this->requestMagicContext($query, $ownerId, $reveal)) {
            return false;
        }

        return true;
    }
}
