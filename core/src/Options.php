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
    abstract public static function data(): array;

    /**
     * Return the request rule.
     *
     * @return string
     */
    abstract public static function rule(): string;

    /**
     * Return the data collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all(): Collection
    {
        return collect(self::data());
    }

    /**
     * Return ready to use select data.
     *
     * @param  string  $nameProp
     * @param  string  $idProp
     * @return \Illuminate\Support\Collection
     */
    public static function select($nameProp = 'name', $idProp = 'id'): Collection
    {
        return self::all()->pluck($nameProp, $idProp);
    }
}
