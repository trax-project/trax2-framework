<?php

namespace Trax\Auth\Stores\Users;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Traits\OwnerScope;

class UserPermissions extends PermissionsProvider
{
    use OwnerScope;
    
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.user';

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => ['user.read.mine'],
        'app' => [],
    ];

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        'user.manage' => \Trax\Auth\Stores\Users\Permissions\ManageUsersPermission::class,
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Users\User  $user
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, User $user): bool
    {
        // No sense for apps.
        if ($consumer->consumerType() == 'app') {
            return false;
        }

        // When the consumer is a user.
        return $consumer->id == $user->id;
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

        return [['id' => $consumer->id]];
    }
}
