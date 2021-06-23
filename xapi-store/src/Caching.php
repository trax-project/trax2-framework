<?php

namespace Trax\XapiStore;

use Illuminate\Support\Facades\Cache;
use Trax\Auth\Caching as AuthCaching;

class Caching extends AuthCaching
{
    /**
     * Conservation delay.
     *
     * @var int
     */
    protected static $xapiConservation = 60 * 60 * 24;

     /**
     * Return an agent.
     *
     * @param  string  $vid
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return \Trax\XapiStore\Stores\Agents\Agent
     */
    public static function agent(string $vid, $callback, $ownerId = null)
    {
        if (!self::enabled()) {
            return $callback($vid, $ownerId);
        }

        $key = isset($ownerId)
            ? 'owner_' . $ownerId . '_agent_' . $vid
            : 'agent_' . $vid;

        return Cache::remember($key, self::$xapiConservation, function () use ($vid, $ownerId, $callback) {
            return $callback($vid, $ownerId);
        });
    }

    /**
     * Return an agent.
     *
     * @param  string  $iri
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return \Trax\XapiStore\Stores\Verbs\Verb
     */
    public static function verb(string $iri, $callback, $ownerId = null)
    {
        if (!self::enabled()) {
            return $callback($iri, $ownerId);
        }

        $key = isset($ownerId)
            ? 'owner_' . $ownerId . '_verb_' . $iri
            : 'verb_' . $iri;
            
        return Cache::remember($key, self::$xapiConservation, function () use ($iri, $ownerId, $callback) {
            return $callback($iri, $ownerId);
        });
    }

    /**
     * Return an agent.
     *
     * @param  string  $iri
     * @param  callable  $callback
     * @param  int  $ownerId
     * @return \Trax\XapiStore\Stores\Activities\Activity
     */
    public static function activity(string $iri, $callback, $ownerId = null)
    {
        if (!self::enabled()) {
            return $callback($iri, $ownerId);
        }

        $key = isset($ownerId)
            ? 'owner_' . $ownerId . '_activity_' . $iri
            : 'activity_' . $iri;
            
        return Cache::remember($key, self::$xapiConservation, function () use ($iri, $ownerId, $callback) {
            return $callback($iri, $ownerId);
        });
    }
}
