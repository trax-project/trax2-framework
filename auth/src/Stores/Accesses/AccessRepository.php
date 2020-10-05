<?php

namespace Trax\Auth\Stores\Accesses;

use Trax\Repo\CrudRepository;
use Trax\Repo\ModelAttributes\ActivableModelRepo;
use Trax\Repo\ModelAttributes\UuidModelRepo;

class AccessRepository extends CrudRepository
{
    use ActivableModelRepo, UuidModelRepo;

    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Accesses\AccessFactory
     */
    public function factory()
    {
        return AccessFactory::class;
    }
}
