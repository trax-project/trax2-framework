<?php

namespace Trax\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Trax\Auth\Authentifier;

class ApiMiddleware
{
    /**
     * The authentication manager.
     *
     * @var \Trax\Auth\Authentifier
     */
    protected $authentifier;
    
    /**
     * Create a new middleware instance.
     *
     * @param  \Trax\Auth\Authentifier  $authentifier
     * @return void
     */
    public function __construct(Authentifier $authentifier)
    {
        $this->authentifier = $authentifier;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        // The source UUID must always be defined for API access.
        $source = $request->route('source');
        if (!$source || $source == 'front') {
            throw new AuthenticationException();
        }

        // Check the access.
        $this->authentifier->checkAccess($source, $request);

        return $next($request);
    }
}
