<?php

namespace Trax\Auth\Contracts;

interface HasPermissionsContract
{
    /**
     * Get the type of consumer. Some permissions are reserved to some types of consumers.
     *
     * @return string
     */
    public function consumerType(): string;

    /**
     * Check if a consumer is an admin and have all permissions.
     *
     * @return int
     */
    public function isAdmin(): int;

    /**
     * Check if a consumer has a given permission.
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool;

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function permissions(): array;

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function booleanPermissions(): array;
}
