<?php

namespace Trax\XapiStore\Stores\StatementCategories;

use Trax\Repo\CrudRepository;
use Trax\XapiStore\Traits\IriBasedRepo;

class StatementCategoryRepository extends CrudRepository
{
    use IriBasedRepo, StatementCategoryFilters;
    
    /**
     * Return model factory.
     *
     * @return \Trax\XapiStore\Stores\StatementCategories\StatementCategoryFactory
     */
    public function factory()
    {
        return StatementCategoryFactory::class;
    }
}
