<?php

namespace Trax\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Trax\Auth\Caching;
use Trax\Auth\Stores\Accesses\AccessService;
use Trax\Auth\Authentifier;

class ApiMiddleware
{
    /**
     * The access repository.
     *
     * @var \Trax\Auth\Stores\Accesses\AccessService
     */
    protected $accesses;

    /**
     * The authentication manager.
     *
     * @var \Trax\Auth\Authentifier
     */
    protected $authentifier;
    
    /**
     * Create a new middleware instance.
     *
     * @param  \Trax\Auth\Stores\Accesses\AccessService  $accesses
     * @param  \Trax\Auth\Authentifier  $authentifier
     * @return void
     */
    public function __construct(AccessService $accesses, Authentifier $authentifier)
    {
        $this->accesses = $accesses;
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

        // Get the access instance from cache first.
        $access = Caching::validateAccess($request, $source, $this);

        // Set the access.
        $this->authentifier->setAccess($access);

        return $next($request);
    }

    /**
     * Get and validate the access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $source
     * @return \Trax\Auth\Stores\Accesses\Access
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function validateAccess(Request $request, string $source)
    {
        $access = $this->accesses->findByUuid($source);

        // Not found.
        if (!$access) {
            throw new AuthenticationException();
        }

        // Check the access is active.
        if (!$access->isActive()) {
            throw new AuthenticationException();
        }

        // Get authorization.
        $guard = $this->authentifier->guard($access->type);
        $authorized = $guard->check($access->credentials, $request);

        // Check authorization.
        if (!$authorized) {
            throw new AuthenticationException();
        }

        return $access;
    }
}
