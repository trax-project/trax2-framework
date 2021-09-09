<?php

namespace Trax\Repo\Contracts;

use Illuminate\Database\Eloquent\Model;
use Trax\Repo\Querying\Query;

interface CrudRepositoryContract extends ReadableRepositoryContract, WritableRepositoryContract
{
    /**
     * Skip Eloquent for get requests.
     *
     * @return void
     */
    public function dontGetWithEloquent();

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

    /**
     * Get the resource after.
     *
     * @param  mixed  $value
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function after($value, string $column = 'id');

    /**
     * Get the resource before.
     *
     * @param  mixed  $value
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function before($value, string $column = 'id');

    /**
     * Finalize a resource before returning it.
     *
     * @param  \Illuminate\Database\Eloquent\Model|object  $resource
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|object
     */
    public function finalize($resource, Query $query = null);
}
