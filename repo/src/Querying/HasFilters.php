<?php

namespace Trax\Repo\Querying;

trait HasFilters
{
    /**
     * Filter.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Get filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * Has a given filter?
     *
     * @param  string  $name
     * @return bool
     */
    public function hasFilter(string $name): bool
    {
        return !is_null($this->filter($name));
    }

    /**
     * Get a given filter.
     *
     * @param  string  $name
     * @return mixed
     */
    public function filter(string $name)
    {
        // Always check the filters before parsing them.
        $this->filters = $this->checkFilters($this->filters);

        foreach ($this->filters as $filter) {
            foreach ($filter as $prop => $value) {
                if ($prop == $name) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Remove a given filter.
     *
     * @param  string  $name
     * @return void
     */
    public function removeFilter(string $name)
    {
        // Always check the filters before parsing them.
        $this->filters = $this->checkFilters($this->filters);

        foreach ($this->filters as $index => &$filter) {
            foreach ($filter as $prop => $value) {
                if ($prop == $name) {
                    unset($filter[$name]);
                    if (empty($filter)) {
                        unset($this->filters[$index]);
                    }
                    return;
                }
            }
        }
    }

    /**
     * Add a filter.
     *
     * @param array  $filter
     * @return void
     */
    public function addFilter(array $filter = [])
    {
        $filters = $this->checkFilters($filter);
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Check filters.
     *
     * @param  array  $filter
     * @return array
     */
    protected function checkFilters(array $filters): array
    {
        // Check that it is a list of conditions, each condition being a list with only one prop.

        // May be usefull for dynamic filters like ['$or' => [...]],
        // which returns [['$or' => [...]]].

        // Or for simple requests like ['filters' => ['name' => 'john', 'age' => '20']].
        // that would return [['name' => 'john'], ['age' => '20']].

        // Something like ['filters' => [['name' => 'john', 'age' => '20']]].
        // would also return [['name' => 'john'], ['age' => '20']].

        // We have an associative array at the first level.
        foreach ($filters as $prop => $value) {
            if (is_string($prop)) {
                return collect($filters)->map(function ($val, $prop) {
                    return [$prop => $val];
                })->values()->all();
            }
        }

        // Check if we have only single conditions.
        $result = [];
        foreach ($filters as $condition) {
            $conditions = collect($condition)->map(function ($val, $prop) {
                return [$prop => $val];
            })->values()->all();
            $result = array_merge($result, $conditions);
        }

        return $result;
    }

    /**
     * Clear filters.
     *
     * @return void
     */
    protected function clearFilters()
    {
        $this->filters = [];
    }
}
