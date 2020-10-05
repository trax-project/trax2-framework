<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;
use Trax\XapiStore\Traits\DocumentFilters;

class AgentProfileRepository extends CrudRepository
{
    use MergeableModelRepo, DocumentFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\AgentProfiles\AgentProfileFactory
     */
    public function factory()
    {
        return AgentProfileFactory::class;
    }

    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'profileId', 'agent', 'since'
        ];
    }
}
