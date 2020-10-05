<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;
use Trax\XapiStore\Traits\DocumentFilters;

class ActivityProfileRepository extends CrudRepository
{
    use MergeableModelRepo, DocumentFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileFactory
     */
    public function factory()
    {
        return ActivityProfileFactory::class;
    }

    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'profileId', 'activityId', 'since'
        ];
    }
}
