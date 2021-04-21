<?php

namespace Trax\XapiStore\XapiLogging;

use Trax\Auth\TraxAuth;

class XapiLogger
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

        $log = new XapiLog();
        $log->api = $api;
        $log->method = $method;

        // Context.
        $context = TraxAuth::context();
        if (isset($context['owner_id'])) {
            $log->owner_id = $context['owner_id'];
        }
        if (isset($context['entity_id'])) {
            $log->entity_id = $context['entity_id'];
        }
        if (isset($context['client_id'])) {
            $log->client_id = $context['client_id'];
        }
        if (isset($context['access_id'])) {
            $log->access_id = $context['access_id'];
        }

        // Batch count.
        if (isset($count)) {
            $log->count = $count;
        }

        // Error.
        if (isset($error)) {
            $log->error = 1;
            $log->data = $error;
        }

        $log->save();
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
            XapiLog::where('owner_id', $ownerId)->delete();
        } else {
            XapiLog::query()->delete();
        }
    }
}
