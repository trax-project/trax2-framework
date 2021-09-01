<?php

namespace Trax\Repo\Traits;

use Trax\Repo\Querying\Query;

trait WritableRepositoryWrapper
{
    /**
     * The wrapped repository.
     *
     * @var \Trax\Repo\Contracts\CrudRepositoryContract
     */
    protected $repository;

    /**
     * Create a new resource.
     *
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing resource, given its ID.
     *
     * @param mixed  $id
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Update an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateModel($model, array $data = null)
    {
        return $this->repository->updateModel($model, $data);
    }

    /**
     * Duplicate an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function duplicateModel($model, array $data = null)
    {
        return $this->repository->duplicateModel($model, $data);
    }

    /**
     * Insert a batch of resource.
     *
     * @param array  $batch
     * @return array
     */
    public function insert(array $batch): array
    {
        return $this->repository->insert($batch);
    }

    /**
     * Delete an existing resource.
     *
     * @param mixed  $id
     * @return void
     */
    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    /**
     * Delete an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleteModel($model)
    {
        return $this->repository->deleteModel($model);
    }

    /**
     * Delete existing resources, given their models.
     *
     * @param \Illuminate\Support\Collection  $models
     * @return void
     */
    public function deleteModels($models)
    {
        return $this->repository->deleteModels($models);
    }

    /**
     * Delete existing resources, given a query.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function deleteByQuery(Query $query)
    {
        return $this->repository->deleteByQuery($query);
    }

    /**
     * Delete the table content without logging individual deletions.
     * It is fast and it resets the auto-increment.
     * But it may fail with foreign keys!
     *
     * @return void
     */
    public function truncate()
    {
        return $this->repository->truncate();
    }
}
