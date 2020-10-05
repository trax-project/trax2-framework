<?php

namespace Trax\Repo\Querying;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Trax\Repo\Contracts\CrudRepositoryContract;

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
     * @param array  $dynamicFilters
     * @return void
     */
    public function __construct(CrudRepositoryContract $repo, string $model, array $dynamicFilters = [])
    {
        $this->grammar = GrammarFactory::make();
        $this->repo = $repo;
        $this->model = $model;
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
            $result = $this->builder()->find($id)->append($query->append());
        } else {
            $result = $this->builder()->find($id);
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
            $result = $this->builder()->findOrFail($id)->append($query->append());
        } else {
            $result = $this->builder()->findOrFail($id);
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

        //print_r($builder->toSql());
        //die;

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
        $builder = $this->queriedBuilder($query, false);

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
        $builder = $this->queriedBuilder($query, false);
        return $builder->count();
    }

    /**
     * Return the query builder with a query already built.
     *
     * @param \Trax\Repo\Querying\Query  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function queriedBuilder(Query $query = null, bool $paginate = true): Builder
    {
        // Simple query with filters.
        if (!isset($query)) {
            $builder = $this->builder();
            $this->processFilters($builder, $this->filters);
            return $builder;
        }

        // Get builder with With.
        $this->query = $query->addFilter($this->filters);
        $builder = $this->builder();

        // Sort results.
        if ($query->sorted()) {
            list($col, $dir) = $query->sortInfo();
            $builder->orderBy($col, $dir);
        }

        // Limit and skip.
        if ($paginate) {
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  array  $filters
     * @return void
     */
    protected function processFilters(Builder $builder, array $filters, bool $or = false)
    {
        $filters = $this->checkFilters($filters);
        $orWhere = false;
        foreach ($filters as $condition) {
            $this->addCondition($builder, $condition, $orWhere);
            $orWhere = $or;
        }
    }

    /**
     * Add a condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  array  $condition
     * @param  bool  $orWhere
     * @return void
     */
    protected function addCondition(Builder $builder, array $condition, bool $orWhere)
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  array  $conditions
     * @param  bool  $orWhere
     * @return void
     */
    protected function addOrCondition(Builder $builder, array $filter, bool $orWhere)
    {
        $where = $orWhere ? 'orWhere' : 'where';
        $builder->$where(function ($builder) use ($filter) {
            $this->processFilters($builder, $filter, true);
        });
    }

    /**
     * Add a logical AND condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  array  $conditions
     * @param  bool  $orWhere
     * @return void
     */
    protected function addAndCondition(Builder $builder, array $filter, bool $orWhere)
    {
        $where = $orWhere ? 'orWhere' : 'where';
        $builder->$where(function ($builder) use ($filter) {
            $this->processFilters($builder, $filter);
        });
    }

    /**
     * Add a filter condition to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $filterProp
     * @param  mixed  $filterValue
     * @param  bool  $orWhere
     * @return void
     */
    protected function addFilterCondition(Builder $builder, string $filterProp, $filterValue, bool $orWhere)
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $prop
     * @param  mixed  $condition
     * @param  bool  $orWhere
     * @return mixed
     */
    protected function addPropertyCondition(Builder $builder, string $prop, $condition, bool $orWhere)
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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function builder(): Builder
    {
        if (!is_null($this->query)) {
            return $this->model::query()->with($this->query->with());
        } else {
            return $this->model::query();
        }
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
