<?php

namespace Trax\XapiStore;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Trax\Auth\Caching as AuthCaching;

class Caching extends AuthCaching
{
     /**
     * Return cache duration.
     *
     * @return int
     */
    public static function duration()
    {
        return config('trax-xapi-store.cache.duration', null);
    }

     /**
     * Cache agents.
     *
     * @param  \Illuminate\Support\Collection  $agents
     * @param  int  $ownerId
     * @return void
     */
    public static function cacheAgents(Collection $agents, $ownerId = null): void
    {
        self::cacheXapiItems($agents, 'agent', $ownerId);
    }

     /**
     * Cache verbs.
     *
     * @param  \Illuminate\Support\Collection  $verbs
     * @param  int  $ownerId
     * @return void
     */
    public static function cacheVerbs(Collection $verbs, $ownerId = null): void
    {
        self::cacheXapiItems($verbs, 'verb', $ownerId);
    }

     /**
     * Cache activities.
     *
     * @param  \Illuminate\Support\Collection  $activities
     * @param  int  $ownerId
     * @return void
     */
    public static function cacheActivities(Collection $activities, $ownerId = null): void
    {
        self::cacheXapiItems($activities, 'activity', $ownerId);
    }

     /**
     * Return an agent.
     *
     * @param  string  $vid
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return int
     */
    public static function agentId(string $vid, $callback, $ownerId = null)
    {
        return self::xapiItemId($vid, 'agent', $callback, $ownerId);
    }

    /**
     * Return a verb.
     *
     * @param  string  $iri
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return int
     */
    public static function verbId(string $iri, $callback, $ownerId = null)
    {
        return self::xapiItemId($iri, 'verb', $callback, $ownerId);
    }

    /**
     * Return an activity.
     *
     * @param  string  $iri
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return int
     */
    public static function activityId(string $iri, $callback, $ownerId = null)
    {
        return self::xapiItemId($iri, 'activity', $callback, $ownerId);
    }

     /**
     * Cache items.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  string $type
     * @param  int  $ownerId
     * @return void
     */
    protected static function cacheXapiItems(Collection $items, string $type, $ownerId = null): void
    {
        if (!self::redisEnabled()) {
            return;
        }
        
        $entries = $items->transform(function ($value) use ($type, $ownerId) {
            return self::xapiKey($value, $type, $ownerId);
        })->flip()->toArray();

        Cache::putMany($entries, self::duration());
    }

    /**
     * Return an xAPI item ID.
     *
     * @param  string  $id
     * @param  string $type
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return int
     */
    protected static function xapiItemId(string $id, string $type, $callback, $ownerId = null)
    {
        if (!self::redisEnabled()) {
            return $callback($id, $ownerId);
        }

        $key = self::xapiKey($id, $type, $ownerId);
            
        return Cache::remember($key, self::duration(), function () use ($id, $ownerId, $callback) {
            return $callback($id, $ownerId);
        });
    }

    /**
     * Return an xAPI key.
     *
     * @param  string  $id
     * @param  string $type
     * @param  int  $ownerId
     * @return string
     */
    protected static function xapiKey(string $id, string $type, $ownerId = null)
    {
        return isset($ownerId)
            ? 'owner_' . $ownerId . '_' . $type . '_' . $id
            : $type . '_' . $id;
    }
}
