<?php

namespace Trax\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Asm89\Stack\CorsService;
use Trax\Auth\Stores\Accesses\AccessService;

class CorsMiddleware
{
    /**
     * The access repository.
     *
     * @var \Trax\Auth\Stores\Accesses\AccessService
     */
    protected $accesses;
    
    /**
     * The CORS service.
     *
     * @var \Asm89\Stack\CorsService $cors
     */
    protected $cors;


    /**
     * Create a new middleware instance.
     *
     * @param  \Trax\Auth\Stores\Accesses\AccessService  $accesses
     * @param  \Asm89\Stack\CorsService  $cors
     * @return void
     */
    public function __construct(AccessService $accesses, CorsService $cors)
    {
        $this->accesses = $accesses;
        $this->cors = $cors;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Go on if this is not a CORS request.
        if (!$this->cors->isCorsRequest($request)) {
            return $next($request);
        }

        // We extract the {source} segment from the URL.
        $fullUrl = $request->fullUrl();
        $urlEnd = \Str::after($fullUrl, url('trax/api'));
        $urlSegments = explode('/', $urlEnd);
        if ($fullUrl == $urlEnd || count($urlSegments) < 3) {
            return $next($request);
        }
        $source = $urlSegments[1];
        if ($source == 'front') {
            return $next($request);
        }

        // We find the access for this source in order to get the cors settings.
        if (!$access = $this->accesses->findByUuid($source)) {
            return $next($request);
        }

        $response = $next($request);

        $response->header('Access-Control-Allow-Origin', $access->cors);
        $response->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Experience-API-Version, If-Match, If-None-Match');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Expose-Headers', 'ETag');

        return $response;
    }
}
