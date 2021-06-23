<?php

namespace Trax\Auth;

use Illuminate\Support\Facades\Cache;
use Trax\Auth\Stores\Accesses\AccessRepository;

class Caching
{
    /**
     * Check if the cache is enabled.
     *
     * @return bool
     */
    public static function enabled()
    {
        return config('cache.default') == 'redis';
    }

    /**
     * Check and return a message.
     *
     * @return string
     */
    public static function checkMessage()
    {
        if (!config('cache.default') == 'redis') {
            return 'TRAX LRS Redis cache is not activated. You should add CACHE_DRIVER=redis to your .env file...';
        }
    
        Cache::put('check', 'check_value', 60);
    
        return Cache::get('check') == 'check_value'
            ? 'TRAX LRS Redis cache is up and running!'
            : 'TRAX LRS Redis cache does not work. Please, check the doc :(';
    }

    /**
     * Return an access.
     *
     * @param  string  $source
     * @return \Trax\Auth\Stores\Accesses\Access
     */
    public static function access(string $source)
    {
        if (!self::enabled()) {
            return app(AccessRepository::class)->findByUuid($source);
        }

        // We keep the access 30 seconds in cache to speed series of requests.
        // The cache is not reset when the access is modified or deleted,
        // so there will be a 30 delay to reflect changes. 
        return Cache::remember("access_$source", 30, function () use ($source) {
            return app(AccessRepository::class)->findByUuid($source);
        });
    }
}
