<?php

namespace Trax\Auth\Permissions;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Contracts\PermissionsProviderContract;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\PermissionsRegistry;

abstract class PermissionsProvider implements PermissionsProviderContract
{
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey;

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => [],   //['client.read.all', 'client.write.mine', 'client.delete.mine'],
        'app' => [],    //['client.read.all'],
    ];

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        // 'client.manage' => \Trax\Auth\Permissions\ManageClientsPermission::class,
    ];

    /**
     * The registry where are stored all the permission instances.
     *
     * @var \Trax\Auth\PermissionsRegistry
     */
    protected $registry;

    /**
     * The permission domain name.
     *
     * @return string
     */
    public function name(): string
    {
        return __($this->langKey . '_name');
    }
    
    /**
     * The permission domain description.
     *
     * @return string
     */
    public function description(): string
    {
        return __($this->langKey . '_description');
    }
    
    /**
     * Set the registry where are stored all the permission instances.
     *
     * @param \Trax\Auth\PermissionsRegistry  $registry
     * @return void
     */
    public function setRegistry(PermissionsRegistry $registry): void
    {
        $this->registry = $registry;
    }
    
    /**
     * Get the permission classes of this provider.
     *
     * @return array
     */
    public function permissionClasses(): array
    {
        return $this->permissionClasses;
    }
    
    /**
     * Get the assignable permissions, given a consumer type.
     *
     * @param string  $consumerType
     * @return \Trax\Auth\Contracts\PermissionContract[]
     */
    public function assignablePermissions(string $consumerType = null): array
    {
        return collect($this->permissionClasses)->map(function ($class, $key) use ($consumerType) {
            
            $permission = $this->registry->permission($key);
            $supportedTypes = $permission->supportedConsumerTypes();

            if (!isset($consumerType) || in_array($consumerType, $supportedTypes)) {
                return [
                    'name' => $permission->name(),
                    'description' => $permission->description(),
                ];
            }
            return false;
        })->filter()->all();
    }
    
    /**
     * Check if a consumer has a permission or capability.
     *
     * @param string  $permissionOrCapability  When it is a capability, the scope is not provided.
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model|null  $resource
     * @return bool
     */
    public function check(string $permissionOrCapability, HasPermissionsContract $consumer, Model $resource = null): bool
    {
        // Admin are always allowed.
        if ($consumer->isAdmin()) {
            return true;
        }

        // Check a simple permission.
        if ($this->isPermission($permissionOrCapability)) {
            return $consumer->hasPermission($permissionOrCapability);
        }

        // Get the higher matching scope.
        $scope = $this->registry->highestScope(
            $permissionOrCapability,
            $this->defaultCapabilities[$consumer->consumerType()],
            $consumer->permissions()
        );

        // No scope.
        if (!$scope) {
            return false;
        }

        // Always allowed.
        if ($scope == 'all') {
            return true;
        }

        // When no resource is specified, there is always something that can be written, read or deleted.
        if (!isset($resource)) {
            return true;
        }
        
        // Call a scoped check method.
        $method = $scope;
        if (!method_exists($this, $method)) {
            return false;
        }
        return $this->$method($consumer, $resource);
    }
    
    /**
     * Get a filter that should be applied to all requests in order to return only allowed items.
     * Return null when no resource should be returned.
     *
     * @param string  $domain
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function scopeFilter(HasPermissionsContract $consumer, string $domain)
    {
        // Admin are allowed to access all resources.
        if ($consumer->isAdmin()) {
            return [];
        }

        // Get the higher read scope.
        $scope = $this->registry->highestScope(
            $domain . '.read',
            $this->defaultCapabilities[$consumer->consumerType()],
            $consumer->permissions()
        );

        // No scope.
        if (!$scope) {
            return null;
        }

        // Always allowed.
        if ($scope == 'all') {
            return [];
        }

        // Call a filter method.
        $method = $scope . 'Filter';
        if (!method_exists($this, $method)) {
            return null;
        }
        return $this->$method($consumer);
    }

    /**
     * Is this string a permission, and not a capability?
     *
     * @param string  $permissionOrCapability
     * @return bool
     */
    protected function isPermission(string $permissionOrCapability): bool
    {
        return isset($this->permissionClasses[$permissionOrCapability]);
    }
}
