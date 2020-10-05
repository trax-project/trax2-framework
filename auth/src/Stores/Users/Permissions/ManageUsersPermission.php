<?php

namespace Trax\Auth\Stores\Users\Permissions;

use Trax\Auth\Permissions\Permission;

class ManageUsersPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.manage_users';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'user.read.owner', 'user.write.owner', 'user.delete.owner',
        'role.read.owner',
        'entity.read.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user', 'app'];
}
