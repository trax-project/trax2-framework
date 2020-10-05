<?php

namespace Trax\Auth\Stores\BasicHttp;

use Trax\Repo\CrudRepository;

class BasicHttpProvider extends CrudRepository
{
    /**
     * Return model factory.
     *
     * @return \Trax\Auth\Stores\BasicHttp\BasicHttpFactory
     */
    public function factory()
    {
        return BasicHttpFactory::class;
    }
}
