<?php

namespace Trax\Auth;

class PermissionScopes
{
    /**
     * Get the scopes names, from the lower to the higher.
     *
     * @return array
     */
    public static function names(): array
    {
        return config('trax-auth.permissions.scopes', ['mine', 'access', 'client', 'entity', 'owner', 'all']);
    }

    /**
     * Get the scopes levels.
     *
     * @return array
     */
    public static function levels(): array
    {
        return array_flip(self::names());
    }

    /**
     * Get the higher scope given a list of scopes.
     *
     * @param array  $scopes
     * @return string
     */
    public static function highestIn(array $scopes): string
    {
        $scopeLevels = array_flip(self::names());
        $levels = collect($scopes)->map(function ($scope) use ($scopeLevels) {
            return $scopeLevels[$scope];
        })->all();
        return self::names()[max($levels)];
    }

    /**
     * Get the lower scope given a list of scopes.
     *
     * @param array  $scopes
     * @return string
     */
    public static function lowestIn($scopes): string
    {
        $scopeLevels = array_flip(self::names());
        $levels = collect($scopes)->map(function ($scope) use ($scopeLevels) {
            return $scopeLevels[$scope];
        })->all();
        return self::names()[min($levels)];
    }
}
