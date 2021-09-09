<?php

namespace Trax\Repo\Contracts;

use Trax\Repo\Querying\Query;

interface WritableRepositoryContract
{
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
}
