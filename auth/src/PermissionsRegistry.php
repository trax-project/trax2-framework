<?php

namespace Trax\Auth;

use Trax\Auth\Contracts\PermissionContract;

class PermissionsRegistry
{
    /**
     * The permission instances, indexed by their names.
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Register and resolve a set of permissions.
     *
     * @param array  $permissionClasses
     * @return void
     */
    public function register(array $permissionClasses): void
    {
        foreach ($permissionClasses as $key => $class) {
            $this->permissions[$key] = new $class();
        }
    }
    
    /**
     * Get a permission instance.
     *
     * @param string  $key
     * @return \Trax\Auth\Contracts\PermissionContract
     */
    public function permission(string $key): PermissionContract
    {
        return $this->permissions[$key];
    }
    
    /**
     * Get the higher scope given a domain, operation, default caps and permssions.
     *
     * @param string  $context  domain.operation
     * @param array  $defaultCapabilities
     * @param array  $permissions
     * @return string|false
     */
    public function highestScope(string $context, array $defaultCapabilities, array $permissions)
    {
        // First, get the filtered default scopes.
        $scopes = $this->filterScopes($defaultCapabilities, $context);

        // Then, get scopes from permissions.
        foreach ($permissions as $key) {
            $capabilities = $this->permission($key)->capabilities();
            $scopes = array_merge(
                $scopes,
                $this->filterScopes($capabilities, $context)
            );
        }

        // No scope found.
        if (empty($scopes)) {
            return false;
        }

        // Now, keep the higher scope.
        return PermissionScopes::highestIn($scopes);
    }
    
    /**
     * Return a list of matching scopes, given a list of capabilities and a capability filter (domain.operation).
     *
     * @param array  $capabilities
     * @param string  $filter
     * @return array
     */
    protected function filterScopes(array $capabilities, string $filter): array
    {
        return collect($capabilities)->map(function ($capability) use ($filter) {

            if (strpos($capability, $filter) !== 0) {
                return false;
            }
            return explode('.', $capability)[2];
        })->filter()->all();
    }
}
