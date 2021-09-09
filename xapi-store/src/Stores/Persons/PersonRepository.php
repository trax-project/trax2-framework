<?php

namespace Trax\XapiStore\Stores\Persons;

use Illuminate\Support\Collection;
use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;

class PersonRepository extends CrudRepository
{
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Persons\PersonFactory
     */
    public function factory()
    {
        return PersonFactory::class;
    }

    /**
     * Find a collection of persons given their UUIDs.
     *
     * @param  array  $uuids
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function whereUuidIn(array $uuids, Query $query = null): Collection
    {
        $ownerId = TraxAuth::context('owner_id', $query);
        return $this->addFilter(['uuid' => ['$in' => $uuids], 'owner_id' => $ownerId])->get();
    }

    /**
     * Insert persons and returns models.
     *
     * @param  array  $data
     * @return \Illuminate\Support\Collection
     */
    public function insertAndGet(array $data): Collection
    {
        $insertedBatch = $this->insert($data);

        // Get back the models.
        $uuids = collect($insertedBatch)->pluck('uuid')->toArray();
        return $this->whereUuidIn($uuids);
    }
}
