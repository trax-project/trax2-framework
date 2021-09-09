<?php

namespace Trax\Repo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Trax\Repo\Contracts\CrudRepositoryContract;
use Trax\Repo\Querying\EloquentQueryWrapper;
use Trax\Repo\Querying\Query;

abstract class CrudRepository implements CrudRepositoryContract
{
    /**
     * Query builder.
     *
     * @var \Trax\Repo\Querying\EloquentQueryWrapper
     */
    protected $builder;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->builder = new EloquentQueryWrapper(
            $this,
            $this->factory()::modelClass(),
            $this->table(),
            $this->dynamicFilters()
        );
    }

    /**
     * Skip Eloquent for get requests.
     *
     * @return void
     */
    public function dontGetWithEloquent()
    {
        $this->builder->dontGetWithEloquent();
    }

    /**
     * Return an Eloquent model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model(): Model
    {
        $modelClass = $this->factory()::modelClass();
        return new $modelClass;
    }

    /**
     * Return the DB table.
     *
     * @return string
     */
    public function table(): string
    {
        return $this->model()->getTable();
    }

    /**
     * Return model factory.
     *
     * @return \Trax\Repo\Contracts\ModelFactoryContract
     */
    abstract public function factory();

    /**
     * Get the dynamic filters.
     *
     * @return array
     */
    public function dynamicFilters(): array
    {
        return [
            'prop_eq',      // You SHOULD define this. This one is just for test.
        ];
    }

    /**
     * Add a filter.
     *
     * @param array  $filter
     * @return \Trax\Repo\Contracts\CrudRepositoryContract
     */
    public function addFilter(array $filter = [])
    {
        $this->builder->addFilter($filter);
        return $this;
    }

    /**
     * Remove filters and return them.
     *
     * @return array
     */
    public function removeFilters(): array
    {
        return $this->builder->removeFilters();
    }

    /**
     * Create a new resource.
     *
     * @param array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        $resource = $this->factory()::make($data);
        $resource->save();
        return $resource;
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
        $resource = $this->findOrFail($id);
        return $this->updateModel($resource, $data);
    }

    /**
     * Update an existing resource, given its model and new data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateModel($model, array $data = null)
    {
        if (isset($data)) {
            $model = $this->factory()::update($model, $data);
        } else {
            $model->save();
        }
        return $model;
    }

    /**
     * Update existing resources, given a query.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @param array  $data
     * @return void
     */
    public function updateByQuery(Query $query, array $data)
    {
        $this->builder->update($query, $data);
    }

    /**
     * Duplicate an existing resource, given its model and new data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function duplicateModel($model, array $data = null)
    {
        return $this->factory()::duplicate($model, $data);
    }

    /**
     * Insert a batch of resource.
     *
     * @param array  $batch
     * @return array
     */
    public function insert(array $batch): array
    {
        $preparedBatch = array_map(function ($data) {
            return $this->factory()::prepare($data);
        }, $batch);
        $this->factory()::modelClass()::insert($preparedBatch);
        return $preparedBatch;
    }

    /**
     * Find an existing resource given its ID.
     *
     * @param mixed  $id
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, Query $query = null)
    {
        return $this->builder->find($id, $query);
    }

    /**
     * Find an existing resource given its ID.
     *
     * @param mixed  $id
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFail($id, Query $query = null)
    {
        return $this->builder->findOrFail($id, $query);
    }

    /**
     * Delete an existing resource.
     *
     * @param mixed  $id
     * @return void
     */
    public function delete($id)
    {
        $resource = $this->findOrFail($id);
        return $this->deleteModel($resource);
    }

    /**
     * Delete an existing resource, given its model.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleteModel($model)
    {
        return $model->delete();
    }

    /**
     * Delete existing resources, given their models.
     *
     * @param \Illuminate\Support\Collection  $models
     * @return void
     */
    public function deleteModels($models)
    {
        $ids = $models->pluck('id')->toArray();
        return $this->factory()::modelClass()::destroy($ids);
    }

    /**
     * Delete existing resources, given a query.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function deleteByQuery(Query $query)
    {
        $this->builder->delete($query);
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
        return $this->factory()::modelClass()::truncate();
    }

    /**
     * Get all resources.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(): Collection
    {
        return $this->get();
    }

    /**
     * Get resources.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function get(Query $query = null): Collection
    {
        return $this->builder->get($query);
    }

    /**
     * Count resources, limited to pagination when provided.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function count(Query $query = null): int
    {
        return $this->builder->count($query);
    }

    /**
     * Count all resources, without pagination params.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function countAll(Query $query = null): int
    {
        return $this->builder->countAll($query);
    }

    /**
     * Get the resource after.
     *
     * @param  mixed  $value
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function after($value, string $column = 'id')
    {
        $resources = $this->get(new Query([
            'after' => [$column => $value],
            'limit' => 1,
        ]));
        if ($resources->count() == 1) {
            // Get may return objects, not Eloquent models.
            // So we don't return directly the last resource.
            return $this->find($resources->last()->id);
        }
        return false;
    }

    /**
     * Get the resource before.
     *
     * @param  mixed  $value
     * @param  string  $column
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function before($value, string $column = 'id')
    {
        $resources = $this->get(new Query([
            'before' => [$column => $value],
            'limit' => 1,
        ]));
        if ($resources->count() == 1) {
            // Get may return objects, not Eloquent models.
            // So we don't return directly the last resource.
            return $this->find($resources->last()->id);
        }
        return false;
    }

    /**
     * Finalize a resource before returning it.
     *
     * @param  \Illuminate\Database\Eloquent\Model|object  $resource
     * @param  \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|object
     */
    public function finalize($resource, Query $query = null)
    {
        return $resource;
    }

    /**
     * Filter: this one is just for test.
     *
     * @param  array  $params
     * @param  \Trax\Repo\Querying\Query  $query
     * @return array
     */
    public function propEqFilter(array $params, Query $query = null)
    {
        return [
            [$params['name'] => $params['value']]
        ];
    }
}
