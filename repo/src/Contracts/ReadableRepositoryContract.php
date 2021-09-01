<?php

namespace Trax\Repo\Contracts;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;

interface ReadableRepositoryContract
{
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
}
