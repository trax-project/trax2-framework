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
     *
     * @return string
     */
    abstract public function rule(): string;

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
