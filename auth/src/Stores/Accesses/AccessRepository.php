<?php

namespace Trax\Auth\Stores\Accesses;

use Trax\Repo\CrudRepository;
use Trax\Repo\ModelAttributes\ActivableModelRepo;

class AccessRepository extends CrudRepository
{
    use ActivableModelRepo;

    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\Accesses\AccessFactory
     */
    public function factory()
    {
        return AccessFactory::class;
    }

    /**
     * Find an existing resource given its UUID.
     *
     * @param string  $uuid
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByUuid($uuid)
    {
        return $this->model()->with('client')->where('uuid', $uuid)->first();
    }
}
