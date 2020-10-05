<?php

namespace Trax\XapiStore\Stores\Verbs;

use Trax\Repo\CrudRepository;

class VerbRepository extends CrudRepository
{
    use VerbFilters;
    
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\Verbs\VerbFactory
     */
    public function factory()
    {
        return VerbFactory::class;
    }
}
