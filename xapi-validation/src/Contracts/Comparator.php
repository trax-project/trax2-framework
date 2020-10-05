<?php

namespace Trax\XapiValidation\Contracts;

interface Comparator
{
    /**
     * Compare 2 xAPI data sources.
     *
     * @param  mixed  $source1
     * @param  mixed  $source2
     * @return bool
     */
    public static function compare($source1, $source2): bool;
}
