<?php

namespace Trax\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Caching
{
    /**
     * Check if the cache is enabled.
     *
     * @return bool
     */
    public static function redisEnabled()
    {
        return config('cache.default') == 'redis';
    }

    /**
     * Check and return a message.
     *
     * @return string
     */
    public static function checkRedis()
    {
        if (!config('cache.default') == 'redis') {
            return 'TRAX LRS Redis cache is not activated. You should add CACHE_DRIVER=redis to your .env file...';
        }
    
        Cache::store('redis')->put('check', 'check_value', 60);
        
        return Cache::store('redis')->get('check') == 'check_value'
            ? 'TRAX LRS Redis cache is up and running!'
            : 'TRAX LRS Redis cache does not work. Please, check the doc :(';
    }

    /**
     * Validate and return an access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $source
     * @param  \Trax\Auth\Middleware\ApiMiddleware $middleware
     * @return \Trax\Auth\Stores\Accesses\Access
     */
    public static function validateAccess(Request $request, string $source, $middleware)
    {
        // ALways cache the access, be it in a file.

        // We keep the access 30 seconds in cache to speed series of requests.
        // The cache is not reset when the access is modified or deleted,
        // so there will be a 30 delay to reflect changes.

        // We check the request credentials only when we load again the access from DB
        // because it speeds up performances without big risk.

        return Cache::remember("access_$source", 30, function () use ($request, $source, $middleware) {
            return $middleware->validateAccess($request, $source);
        });
    }
}
