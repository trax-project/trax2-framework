<?php

namespace Trax\Auth\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Trax\Auth\Stores\Accesses\AccessService;

class KnownAccessMiddleware
{
    /**
     * The access repository.
     *
     * @var \Trax\Auth\Stores\Accesses\AccessService
     */
    protected $accesses;

    /**
     * Create a new middleware instance.
     *
     * @param  \Trax\Auth\Stores\Accesses\AccessService  $accesses
     * @return void
     */
    public function __construct(AccessService $accesses)
    {
        $this->accesses = $accesses;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        // We check only API routes with a source.
        $source = $request->route('source');
        if (!$source || $source == 'front') {
            return $next($request);
        }

        // Get the access from the cache first.
        $access = $this->accesses->findByUuid($source);

        // Not found.
        if (!$access) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}
