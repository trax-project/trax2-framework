<?php

namespace Trax\XapiStore\Stores\ActivityTypes;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\IriBasedRepo;

class ActivityTypeRepository extends CrudRepository
{
    use IriBasedRepo, ActivityTypeFilters;
    
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\ActivityTypes\ActivityTypeFactory
     */
    public function factory()
    {
        return ActivityTypeFactory::class;
    }
}
