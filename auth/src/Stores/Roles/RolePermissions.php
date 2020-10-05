<?php

namespace Trax\Auth\Stores\Roles;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Traits\OwnerScope;

class RolePermissions extends PermissionsProvider
{
    use OwnerScope;
    
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.role';

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => ['role.read.mine'],
        'app' => [],
    ];

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        'role.manage' => \Trax\Auth\Stores\Roles\Permissions\ManageRolesPermission::class,
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Roles\Role  $role
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, Role $role): bool
    {
        // No sense for apps.
        if ($consumer->consumerType() == 'app') {
            return false;
        }

        // When the consumer is an access.
        return $consumer->role_id == $role->id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function mineFilter(HasPermissionsContract $consumer)
    {
        // No sense for apps.
        if ($consumer->consumerType() == 'app') {
            return null;
        }

        return [['id' => $consumer->role_id]];
    }
}
