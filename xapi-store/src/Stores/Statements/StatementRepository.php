<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;

class StatementRepository extends CrudRepository
{
    use XapiStatementRepository, StatementFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Statements\StatementFactory
     */
    public function factory()
    {
        return StatementFactory::class;
    }

    /**
     * Find an existing statement given its UUID.
     *
     * @param  string  $uuid
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function findByUuid(string $uuid, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uuid' => $uuid, 'owner_id' => $ownerId])->get()->first();
    }

    /**
     * Find a collection of statements given their UUIDs.
     *
     * @param  array  $uuids
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereUuidIn(array $uuids, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uuid' => ['$in' => $uuids], 'owner_id' => $ownerId])->get();
    }
}
