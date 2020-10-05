<?php

namespace Trax\Auth\Stores\Owners;

use Trax\Repo\CrudRepository;
use Trax\Repo\ModelAttributes\UuidModelRepo;

class OwnerRepository extends CrudRepository
{
    use UuidModelRepo;
    
    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Owners\OwnerFactory
     */
    public function factory()
    {
        return OwnerFactory::class;
    }
}
