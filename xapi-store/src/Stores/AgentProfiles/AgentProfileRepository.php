<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\MergeableModelRepo;

class AgentProfileRepository extends CrudRepository
{
    use MergeableModelRepo, AgentProfileFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\AgentProfiles\AgentProfileFactory
     */
    public function factory()
    {
        return AgentProfileFactory::class;
    }
}
