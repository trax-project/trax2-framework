<?php

namespace Trax\Core;

use Illuminate\Support\Collection;

class Helpers
{
    /**
     * Transform a collection to a ready to use select data.
     *
     * @param  string  $nameProp
     * @param  string  $idProp
     * @param  \Illuminate\Support\Collection  $collection
     * @return object
     */
    public static function select(Collection $collection, $nameProp = 'name', $idProp = 'id')
    {
        $data = $collection->pluck($nameProp, $idProp);
        return $data->isEmpty() ? (new \stdClass()) : $data;
    }
}
