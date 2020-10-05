<?php

namespace Trax\Auth\Stores\Entities;

use Trax\Repo\CrudRepository;
use Trax\Repo\ModelAttributes\UuidModelRepo;

class EntityRepository extends CrudRepository
{
    use UuidModelRepo;
    
    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Entities\EntityFactory
     */
    public function factory()
    {
        return EntityFactory::class;
    }
}
