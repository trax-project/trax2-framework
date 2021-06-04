<?php

namespace Trax\XapiStore\Stores\States;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;

class StateRepository extends CrudRepository
{
    use MergeableModelRepo, StateFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\States\StateFactory
     */
    public function factory()
    {
        return StateFactory::class;
    }
}
