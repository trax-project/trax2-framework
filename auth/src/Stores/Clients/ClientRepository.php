<?php

namespace Trax\Auth\Stores\Clients;

use Trax\Repo\CrudRepository;
use Trax\Repo\ModelAttributes\ActivableModelRepo;

class ClientRepository extends CrudRepository
{
    use ActivableModelRepo;

    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Clients\ClientFactory
     */
    public function factory()
    {
        return ClientFactory::class;
    }
}
