<?php

namespace Trax\Core;

use Illuminate\Support\Collection;

abstract class Options
{
    /**
     * Return the data.
     *
     * @return array
     */
    abstract public function data(): array;

    /**
     * Return the request rule.
     * The default behavior considers the items 'id' as possible values.
     *
     * @return string
     */
    public function rule(): string
    {
        $values = $this->all()->implode('id', ',');
        return 'string|in:' . $values;
    }

    /**
     * Return the data collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get(): Collection
    {
        return collect($this->data());
    }

    /**
     * Return the data collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(): Collection
    {
        return collect($this->data());
    }

    /**
     * Return ready to use select data.
     *
     * @param  string  $nameProp
     * @param  string  $idProp
     * @return \Illuminate\Support\Collection
     */
    public function select($nameProp = 'name', $idProp = 'id'): Collection
    {
        return $this->all()->pluck($nameProp, $idProp);
    }
}
