<?php

namespace Trax\Auth\Stores\Users;

use Trax\Repo\CrudRepository;
use Trax\Repo\ModelAttributes\ActivableModelRepo;
use Trax\Repo\ModelAttributes\UuidModelRepo;

class UserRepository extends CrudRepository
{
    use ActivableModelRepo, UuidModelRepo, UserFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Users\UserFactory
     */
    public function factory()
    {
        return UserFactory::class;
    }
}
