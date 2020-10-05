<?php

namespace Trax\XapiValidation\Contracts;

interface Formatter
{
    /**
     * Format a statement.
     *
     * @param  \stdClass|\Illuminate\Database\Eloquent\Model  $statement
     * @param  string  $format
     * @param  string  $lang
     * @return \stdClass|\Illuminate\Database\Eloquent\Model
     */
    public static function format($statement, string $format = 'exact', $lang = null);
}
