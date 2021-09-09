<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\XapiDate;

class StatementRepository extends CrudRepository
{
    use XapiStatementRepository, StatementFilters;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        // We don't need Eloquent for pure JSON queries. We skip it to improve performances.
        $this->dontGetWithEloquent = !config('trax-xapi-store.requests.relational', false);
        parent::__construct();
    }

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

    /**
     * Find a collection of statements given their statement IDs.
     *
     * @param  array  $ids
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereStatementIdIn(array $ids, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uuid' => ['$in' => $ids], 'owner_id' => $ownerId])->get();
    }
    
    /**
     * Get the consistent through value.
     *
     * @return string
     */
    public function consistentThrough()
    {
        // Get the `stored` of the oldest pending statement.
        if ($pendingStatement = $this->get(new Query([
            'filters' => ['pending' => true],
            'sort' => ['id'],
            'limit' => 1,
        ]))->first()) {
            return $pendingStatement->data->stored;
        }

        // No pending statement.
        return XapiDate::now();
    }
}
