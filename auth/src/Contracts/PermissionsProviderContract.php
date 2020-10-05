<?php

namespace Trax\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\PermissionsRegistry;

interface PermissionsProviderContract
{
    /**
     * The permission domain name.
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * The permission domain description.
     *
     * @return string
     */
    public function description(): string;
    
    /**
     * Set the registry where are stored all the permission instances.
     *
     * @param \Trax\Auth\PermissionsRegistry  $registry
     * @return void
     */
    public function setRegistry(PermissionsRegistry $registry): void;
    
    /**
     * Get the permission classes of this provider.
     *
     * @return array
     */
    public function permissionClasses(): array;
    
    /**
     * Get the assignable permissions, given a consumer type.
     *
     * @param string  $consumerType
     * @return \Trax\Auth\Contracts\PermissionContract[]
     */
    public function assignablePermissions(string $consumerType = null): array;
    
    /**
     * Check if a consumer has a permission or capability.
     *
     * @param string  $permissionOrCapability  When it is a capability, the scope is not provided.
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model|null  $resource
     * @return bool
     */
    public function check(string $permissionOrCapability, HasPermissionsContract $consumer, Model $resource = null): bool;
    
    /**
     * Get a filter that should be applied to all requests in order to return only allowed items.
     * Return null when no resource should be returned.
     *
     * @param string  $domain
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function scopeFilter(HasPermissionsContract $consumer, string $domain);
}
