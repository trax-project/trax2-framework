<?php

namespace Trax\Auth\Contracts;

interface StorePermissionsContract extends HasPermissionsContract
{
    /**
     * Update all the permissions of a model.
     *
     * @param array  $permissions  The list a permissions
     * @return void
     */
    public function updatePermissions(array $permissions): void;

    /**
     * Update all the permissions of a model.
     *
     * @param array  $permissions  An associative array with permissions as keys and activation (boolean) as values
     * @return void
     */
    public function updateBooleanPermissions(array $permissions): void;
}
