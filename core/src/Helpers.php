<?php

namespace Trax\Core;

use Illuminate\Support\Collection;

class Helpers
{
    /**
     * Transform a collection to a ready to use select data.
     *
     * @param  \Illuminate\Support\Collection  $collection
     * @param  string  $nameProp
     * @param  string  $idProp
     * @return \Illuminate\Support\Collection
     */
    public static function select(Collection $collection, $nameProp = 'name', $idProp = 'id'): Collection
    {
        return $collection->pluck($nameProp, $idProp);
    }
}
