<?php

namespace Trax\Auth\Test\Utils;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Trax\Auth\Authorizer;

trait MockAuthorizer
{
    use RefreshDatabase;
    
    /**
     * Middlewares are deactivated to bypass authentication.
     * The authorizer is mocked.
     */
    protected function mockAuthorizer()
    {
        $this->mock(Authorizer::class, function ($mock) {
            $mock->shouldReceive('can');
            $mock->shouldReceive('must');
            $mock->shouldReceive('scopeFilter')->andReturn([]);
            $mock->shouldReceive('permissions')->andReturn([]);
        });
    }
}
