<?php

namespace Trax\XapiStore\Stores\States;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;
use Trax\XapiStore\Traits\DocumentFilters;

class StateRepository extends CrudRepository
{
    use MergeableModelRepo, DocumentFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\States\StateFactory
     */
    public function factory()
    {
        return StateFactory::class;
    }
    
    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'stateId', 'activityId', 'agent', 'since'
        ];
    }
}
