<?php

namespace Trax\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        // We find the access for this source in order to get the cors settings.
        if (!$this->accesses->findByUuid($source)) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}
