<?php

namespace Trax\Repo\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;

interface CrudRepositoryContract
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
     * Create a new resource.
     *
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update an existing resource, given its ID.
     *
     * @param mixed  $id
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data);

    /**
     * Update an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateModel($model, array $data = null);

    /**
     * Duplicate an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function duplicateModel($model, array $data = null);

    /**
     * Insert a batch of resource.
     *
     * @param array  $batch
     * @return array
     */
    public function insert(array $batch): array;

    /**
     * Find an existing resource given its ID.
     *
     * @param mixed  $id
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, Query $query = null);

    /**
     * Find an existing resource given its ID.
     *
     * @param mixed  $id
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFail($id, Query $query = null);

    /**
     * Delete an existing resource.
     *
     * @param mixed  $id
     * @return void
     */
    public function delete($id);

    /**
     * Delete an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleteModel($model);

    /**
     * Delete existing resources, given their models.
     *
     * @param \Illuminate\Support\Collection  $models
     * @return void
     */
    public function deleteModels($models);

    /**
     * Delete existing resources, given a query.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function deleteByQuery(Query $query);

    /**
     * Delete the table content without logging individual deletions.
     * It is fast and it resets the auto-increment.
     * But it may fail with foreign keys!
     *
     * @return void
     */
    public function truncate();
    
    /**
     * Get all resources.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(): Collection;

    /**
     * Get resources.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function get(Query $query = null): Collection;

    /**
     * Count resources, limited to pagination when provided.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function count(Query $query = null): int;

    /**
     * Count all resources, without pagination params.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function countAll(Query $query = null): int;

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
