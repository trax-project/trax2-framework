<?php

namespace Trax\XapiStore\Middleware;

use Illuminate\Http\Request;
use Trax\Core\Validation;
use Trax\XapiStore\HttpRequest;
use Trax\XapiStore\Exceptions\XapiBadRequestException;

class XapiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        // Before actions: check headers
        $this->checkXapiHeader($request);
        
        // Fine, we continue
        $response =  $next($request);
        
        // Add xAPI header to responses.
        $response->header('X-Experience-API-Version', '1.0.3');
        
        return $response;
    }

    /**
     * Check X-Experience-API-Version header.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    public function checkXapiHeader(Request $request)
    {
        // No version
        if (!HttpRequest::hasHeader($request, 'X-Experience-API-Version')) {
            throw new XapiBadRequestException('Missing X-Experience-API-Version header.');
        }

        // Wrong format
        $version = HttpRequest::header($request, 'X-Experience-API-Version');
        if (!Validation::check($version, 'xapi_version')) {
            throw new XapiBadRequestException("Incorrect X-Experience-API-Version header: [$version].");
        }
    }
}
