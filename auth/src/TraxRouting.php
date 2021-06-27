<?php

namespace Trax\Auth;

use Illuminate\Support\Facades\Facade;

class TraxRouting extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Routing::class;
    }
}
