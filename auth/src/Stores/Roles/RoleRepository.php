<?php

namespace Trax\Auth\Stores\Roles;

use Trax\Repo\CrudRepository;

class RoleRepository extends CrudRepository
{
    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Roles\RoleFactory
     */
    public function factory()
    {
        return RoleFactory::class;
    }
}
