<?php

namespace Trax\Auth\Test\Utils;

use Illuminate\Foundation\Testing\WithoutMiddleware;

trait NoAuth
{
    use WithoutMiddleware, MockAuthorizer;
}
