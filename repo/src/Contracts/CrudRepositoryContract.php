<?php

namespace Trax\Repo\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CrudRepositoryContract extends ReadableRepositoryContract, WritableRepositoryContract
{
    /**
     * Return an Eloquent model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model(): Model;

    /**
     * Return the DB table.
     *
     * @return string
     */
    public function table(): string;
    
    /**
     * Return model factory.
     *
     * @return \Trax\Repo\Contracts\ModelFactoryContract
     */
    public function factory();

    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array;
    
    /**
     * Add a filter.
     *
     * @param array  $filter
     * @return \Trax\Repo\Contracts\CrudRepositoryContract
     */
    public function addFilter(array $filter = []);

    /**
     * Remove filters and return them.
     *
     * @return array
     */
    public function removeFilters(): array;
}
