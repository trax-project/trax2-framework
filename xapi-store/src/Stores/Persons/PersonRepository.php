<?php

namespace Trax\XapiStore\Stores\Persons;

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
}
