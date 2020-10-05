<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Repo\CrudRepository;

class AgentRepository extends CrudRepository
{
    use AgentFilters;

    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Agents\AgentFactory
     */
    public function factory()
    {
        return AgentFactory::class;
    }
}
