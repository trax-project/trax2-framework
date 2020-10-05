<?php

namespace Trax\Auth\Stores\Owners;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;

class OwnerPermissions extends PermissionsProvider
{
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.owner';

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => ['owner.read.mine'],
        'app' => ['owner.read.mine'],
    ];

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        'owner.manage' => \Trax\Auth\Stores\Owners\Permissions\ManageOwnersPermission::class,
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Owners\Owner  $owner
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, Owner $owner): bool
    {
        return $consumer->owner_id == $owner->id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function mineFilter(HasPermissionsContract $consumer)
    {
        return [['id' => $consumer->owner_id]];
    }
}
