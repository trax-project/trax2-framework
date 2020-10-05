<?php

namespace Trax\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Trax\Auth\Authorizer;

class PermissionMiddleware
{
    /**
     * The authentication manager.
     *
     * @var \Trax\Auth\Authorizer
     */
    protected $authorizer;

    /**
     * Create a new middleware instance.
     *
     * @param  \Trax\Auth\Authorizer  $authorizer
     * @return void
     */
    public function __construct(Authorizer $authorizer)
    {
        $this->authorizer = $authorizer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $permission
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $this->authorizer->must($permission);

        return $next($request);
    }
}
