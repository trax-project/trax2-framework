<?php

namespace Trax\Repo\Traits;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;

trait ReadableRepositoryWrapper
{
    /**
     * The wrapped repository.
     *
     * @var \Trax\Repo\Contracts\CrudRepositoryContract
     */
    protected $repository;

    /**
     * Find an existing resource given its ID.
     *
     * @param mixed  $id
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, Query $query = null)
    {
        return $this->repository->find($id, $query);
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
        return $this->repository->findOrFail($id, $query);
    }

    /**
     * Get all resources.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get resources.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function get(Query $query = null): Collection
    {
        return $this->repository->get($query);
    }

    /**
     * Count resources, limited to pagination when provided.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function count(Query $query = null): int
    {
        return $this->repository->count($query);
    }

    /**
     * Count all resources, without pagination params.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function countAll(Query $query = null): int
    {
        return $this->repository->countAll($query);
    }
}
