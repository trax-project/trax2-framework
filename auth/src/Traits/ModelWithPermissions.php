<?php

namespace Trax\Auth\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Eloquent models using this trait must define a JSON column
 * casted to array and named 'permissions'.
 */
trait ModelWithPermissions
{
    /**
     * Initialize this trait.
     *
     * @return void
     */
    protected function initializeModelWithPermissions()
    {
        $this->casts['permissions'] = 'array';
        $this->fillable[] = 'permissions';
        $this->attributes['permissions'] = json_encode([]);
    }

    /**
     * Update all the permissions of a model.
     *
     * @param array  $permissions  The list a permissions
     * @return void
     */
    public function updatePermissions(array $permissions): void
    {
        $this->permissions = collect(array_flip($permissions))->map(function ($index) {
            return true;
        })->all();
    }

    /**
     * Update all the permissions of a model.
     *
     * @param array  $permissions  An associative array with permissions as keys and activation (boolean) as values
     * @return void
     */
    public function updateBooleanPermissions(array $permissions): void
    {
        $this->permissions = array_filter($permissions);
    }

    /**
     * Check if a consumer has a given permission.
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return isset($this->permissions[$permission]) && $this->permissions[$permission];
    }

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function permissions(): array
    {
        return array_keys(array_filter($this->permissions));
    }

    /**
     * Get all the permissions of a consumer.
     *
     * @return string[]
     */
    public function booleanPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Scope a query to only include roles with a given permission.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPermission(Builder $query, string $permission)
    {
        return $query->where("permissions->$permission", true);
    }
}
