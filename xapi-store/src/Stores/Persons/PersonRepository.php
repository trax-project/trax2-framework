<?php

namespace Trax\XapiStore\Stores\Persons;

use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;
use Trax\Repo\CrudRepository;
use Trax\XapiStore\Caching;

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
    public function whereUuidIn(array $uuids, Query $query = null)
    {
        $ownerId = TraxAuth::context('owner_id', $query);

        // We should use the caching system here!!!!!!!!!!!!!!!!!

        return $this->addFilter(['uuid' => ['$in' => $uuids], 'owner_id' => $ownerId])->get();
    }
}
