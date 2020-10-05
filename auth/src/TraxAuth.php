<?php

namespace Trax\Auth;

use Illuminate\Support\Facades\Facade;

class TraxAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Authentifier::class;
    }
}
