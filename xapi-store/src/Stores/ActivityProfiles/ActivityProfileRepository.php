<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;

class ActivityProfileRepository extends CrudRepository
{
    use MergeableModelRepo, ActivityProfileFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileFactory
     */
    public function factory()
    {
        return ActivityProfileFactory::class;
    }
}
