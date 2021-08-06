<?php

namespace Trax\Repo\Querying;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Trax\Repo\Contracts\CrudRepositoryContract;
use Illuminate\Support\Facades\Log;

class EloquentQueryWrapper
{
    use HasFilters;
    
    /**
     * DB additional specific grammar.
     *
     * @var \Trax\Repo\Querying\Grammar
     */
    protected $grammar;
    
    /**
     * The calling repository.
     *
     * @var \Trax\Repo\Contracts\CrudRepositoryContract
     */
    protected $repo;

    /**
     * The model associated with the builder.
     *
     * @var string
     */
    protected $model;

    /**
     * DB table when DB query builder is prefered to Eloquent.
     *
     * @var string
     */
    protected $table;

    /**
     * The filters implemented by the repository.
     *
     * @var array
     */
    protected $dynamicFilters;

    /**
     * Query.
     *
     * @var \Trax\Repo\Querying\Query
     */
    protected $query;

    /**
     * Constructor.
     *
     * @param \Trax\Repo\Contracts\CrudRepositoryContract  $repo
     * @param string  $model
     * @param string  $table  May be specified when when want a direct request on the table, not on the Eloquent model
     * @param array  $dynamicFilters
     * @return void
     */
    public function __construct(CrudRepositoryContract $repo, string $model, string $table = null, array $dynamicFilters = [])
    {
        $this->grammar = GrammarFactory::make();
        $this->repo = $repo;
        $this->model = $model;
        $this->table = $table;
        $this->dynamicFilters = $dynamicFilters;
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
        if (isset($query)) {
            $this->query = $query->addFilter($this->filters);
            $result = $this->eloquentBuilder()->find($id)->append($query->append());
        } else {
            $result = $this->eloquentBuilder()->find($id);
        }
        return $this->response($result);
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
        if (isset($query)) {
            $this->query = $query->addFilter($this->filters);
            $result = $this->eloquentBuilder()->findOrFail($id)->append($query->append());
        } else {
            $result = $this->eloquentBuilder()->findOrFail($id);
        }
        return $this->response($result);
    }

    /**
     * Get resources.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Support\Collection
     */
    public function get(Query $query = null): Collection
    {
        $builder = $this->queriedBuilder($query);

        //Log::channel('benchmark')->info($builder->toSql());

        //dd($builder->toSql());
        //dd($builder->getBindings());

        // Get results.
        $result = $builder->get();

        // Append accessors.
        if (isset($query) && !empty($query->append())) {
            $result->each->append($query->append());
        }

        return $this->response($result);
    }

    /**
     * Delete resources.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function delete(Query $query): void
    {
        $builder = $this->queriedBuilder($query, true);

        //print_r($builder->toSql());
        //die;

        $builder->delete();
    }

    /**
     * Count resources, limited to pagination when provided.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function count(Query $query = null): int
    {
        $builder = $this->queriedBuilder($query);
        return $builder->count();
    }

    /**
     * Count all resources, without pagination params.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return int
     */
    public function countAll(Query $query = null): int
    {
        $builder = $this->queriedBuilder($query, true);
        return $builder->count();
    }

    /**
     * Return the query builder with a query already built.
     *
     * @param  \Trax\Repo\Querying\Query  $query
     * @param  bool  $noLimit
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queriedBuilder(Query $query = null, bool $noLimit = false)
    {
        // Simple query with filters.
        if (!isset($query)) {
            $builder = $this->builder();
            $this->processFilters($builder, $this->filters);
            return $builder;
        }

        // Get builder with With and/or more filters.
        $this->query = $query->addFilter($this->filters);
        $builder = $this->builder();

        // Sort results.
        foreach ($query->sortInfo() as $sortInfo) {
            if (is_null($sortInfo['rel'])) {
                // Simple orderBy.
                $builder->orderBy($sortInfo['col'], $sortInfo['dir']);
            } else {
                // Order by applied on a belongTo relation.
                $table = (new $this->model)->getTable();
                $relationName = $sortInfo['rel'];
                $foreignKey = $table . '.' . $relationName . '_id';
                $relation = (new $this->model)->$relationName();
                $joinedTable = $relation->getRelated()->getTable();
                $joinedTableforeignKey = $relation->getRelated()->getQualifiedKeyName();

                $builder->join($joinedTable, $foreignKey, '=', $joinedTableforeignKey)
                    ->orderBy($joinedTable . '.' . $sortInfo['col'], $sortInfo['dir']);
            }
        }

        // Limit and skip.
        if (!$noLimit) {
            $builder->limit($query->limit());
            $builder->skip($query->skip());
        }

        // Before.
        if ($query->hasBefore()) {
            list($col, $val) = $query->beforeInfo();
            $builder->where($col, '<', $val)->orderBy($col, 'desc');
        }

        // After.
        if ($query->hasAfter()) {
            list($col, $val) = $query->afterInfo();
            $builder->where($col, '>', $val)->orderBy($col, 'asc');
        }

        // Filter.
        $this->processFilters($builder, $query->filters());

        return $builder;
    }

    /**
     * Perform a query on a given builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  array  $filters
     * @return void
     */
    protected function processFilters($builder, array $filters, bool $or = false)
    {
        $filters = $this->serializeFilters($filters);
        $orWhere = false;
        foreach ($filters as $condition) {
            $this->addCondition($builder, $condition, $orWhere);
            $orWhere = $or;
        }
    }

    /**
     * Add a condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  array  $condition
     * @param  bool  $orWhere
     * @return void
     */
    protected function addCondition($builder, array $condition, bool $orWhere)
    {
        // We always have 1 condition, but we loop to get the $prop easily.
        foreach ($condition as $prop => $value) {
            if (in_array($prop, Query::keywords())) {
                // Logical operators.
                if ($prop == '$or') {
                    $this->addOrCondition($builder, $value, $orWhere);
                } elseif ($prop == '$and') {
                    $this->addAndCondition($builder, $value, $orWhere);
                }
            } elseif (in_array($prop, $this->dynamicFilters)) {
                // Filter.
                $this->addFilterCondition($builder, $prop, $value, $orWhere);
            } else {
                // Property.
                $this->addPropertyCondition($builder, $prop, $value, $orWhere);
            }
        }
    }

    /**
     * Add a logical OR condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  array  $conditions
     * @param  bool  $orWhere
     * @return void
     */
    protected function addOrCondition($builder, array $filter, bool $orWhere)
    {
        $where = $orWhere ? 'orWhere' : 'where';
        $builder->$where(function ($builder) use ($filter) {
            $this->processFilters($builder, $filter, true);
        });
    }

    /**
     * Add a logical AND condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  array  $conditions
     * @param  bool  $orWhere
     * @return void
     */
    protected function addAndCondition($builder, array $filter, bool $orWhere)
    {
        $where = $orWhere ? 'orWhere' : 'where';
        $builder->$where(function ($builder) use ($filter) {
            $this->processFilters($builder, $filter);
        });
    }

    /**
     * Add a filter condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string  $filterProp
     * @param  mixed  $filterValue
     * @param  bool  $orWhere
     * @return void
     */
    protected function addFilterCondition($builder, string $filterProp, $filterValue, bool $orWhere)
    {
        $where = $orWhere ? 'orWhere' : 'where';
        $builder->$where(function ($builder) use ($filterProp, $filterValue) {
            $method = \Str::camel($filterProp) . 'Filter';
            $filter = $this->repo->$method($filterValue, $this->query);
            $this->processFilters($builder, $filter);
        });
    }

    /**
     * Add a property condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $builder
     * @param  string  $prop
     * @param  mixed  $condition
     * @param  bool  $orWhere
     * @return mixed
     */
    protected function addPropertyCondition($builder, string $prop, $condition, bool $orWhere)
    {
        $where = $orWhere ? 'orWhere' : 'where';

        // Unit tests replace * by __asterisk__
        $prop = str_replace('__asterisk__', '*', $prop);

        // Scalar value (string, integer, etc.) or JSON value.
        // JSON values must be in an array form, with more than 1 property!
        if (!is_array($condition) || count($condition) > 1) {
            $value = $condition;
            if (strpos($prop, '[*]') !== false || is_array($value)) {
                return $this->grammar->addJsonContainsCondition($builder, $prop, $value, $orWhere);
            } else {
                return $builder->$where($prop, $value);
            }
        }
        // Get operator and value.
        foreach ($condition as $operator => $value) {
            break;
        }

        // JSON search.
        if (strpos($prop, '[*]') !== false && $operator == '$text') {
            return $this->grammar->addJsonSearchCondition($builder, $prop, $value, $orWhere);
        }

        // Other operators.
        switch ($operator) {
            case '$eq':
                return $builder->$where($prop, $value);
            case '$gt':
                return $builder->$where($prop, '>', $value);
            case '$gte':
                return $builder->$where($prop, '>=', $value);
            case '$lt':
                return $builder->$where($prop, '<', $value);
            case '$lte':
                return $builder->$where($prop, '<=', $value);
            case '$ne':
                return $builder->$where($prop, '<>', $value);
            case '$in':
                return $builder->{$where.'In'}($prop, $value);
            case '$nin':
                return $builder->{$where.'NotIn'}($prop, $value);
            case '$text':
                return $builder->$where($prop, 'like', '%' . $value . '%');
            case '$exists':
                if ($value) {
                    return $builder->{$where.'NotNull'}($prop);
                } else {
                    return $builder->{$where.'Null'}($prop);
                }
            case '$has':
                return $builder->{$where.'Has'}($prop, $value);
        }
    }

    /**
     * Return the query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function builder()
    {
        // Get the DB query builder when it is prefered to Eloquent.
        if (isset($this->table)) {
            return DB::table($this->table);
        }
        // ELoquent query builder.
        return $this->eloquentBuilder();
    }

    /**
     * Return the Eloquent query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function eloquentBuilder()
    {
        $queryBuilder = $this->model::query();
        if (!is_null($this->query)) {
            $queryBuilder = $queryBuilder->with($this->query->with());
        }
        return $queryBuilder;
    }

    /**
     * Finalize the response.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection  $result
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    protected function response($result)
    {
        if ($result instanceof Collection) {
            $response = $result->map(function ($item) {
                return $this->repo->finalize($item, $this->query);
            });
        } else {
            $response = $this->repo->finalize($result, $this->query);
        }
        $this->reinit();
        return $response;
    }

    /**
     * Reinitialize.
     *
     * @return void
     */
    protected function reinit()
    {
        $this->clearFilters();
        $this->query = null;
    }
}
