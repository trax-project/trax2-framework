<?php

namespace Trax\XapiStore\Stores\Logs;

use Trax\Repo\CrudRepository;

class LogRepository extends CrudRepository
{
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Logs\LogFactory
     */
    public function factory()
    {
        return LogFactory::class;
    }
}
