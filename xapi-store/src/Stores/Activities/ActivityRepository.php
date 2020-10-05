<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;

class ActivityRepository extends CrudRepository
{
    use MergeableModelRepo, ActivityFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Activities\ActivityFactory
     */
    public function factory()
    {
        return ActivityFactory::class;
    }
}
