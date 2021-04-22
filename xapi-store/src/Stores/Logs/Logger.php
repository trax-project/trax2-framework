<?php

namespace Trax\XapiStore\Stores\Logs;

use Trax\Auth\TraxAuth;

class Logger
{
    /**
     * Log an xAPI request.
     *
     * @param  string  $api
     * @param  string  $method
     * @param  integer|null  $count
     * @param  array|null  $error
     * @return void
     */
    public static function log(string $api, string $method, $count = null, $error = null): void
    {
        if (!config('trax-xapi-store.logging.enabled', false)) {
            return;
        }

        $data = ['api' => $api, 'method' => $method];
        $data = array_merge($data, TraxAuth::context());

        if (isset($count)) {
            $data['count'] = $count;
        }

        if (isset($error)) {
            $data['error'] = 1;
            $data['data'] = $error;
        }
        app(LogRepository::class)->create($data);
    }

    /**
     * Clear all logs (from the given store).
     *
     * @param  int|string|null  $ownerId
     * @return void
     */
    public static function clear($ownerId = null): void
    {
        if (isset($ownerId)) {
            Log::where('owner_id', $ownerId)->delete();
        } else {
            Log::query()->delete();
        }
    }
}
