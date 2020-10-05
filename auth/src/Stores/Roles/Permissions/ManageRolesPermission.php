<?php

namespace Trax\Auth\Stores\Roles\Permissions;

use Trax\Auth\Permissions\Permission;

class ManageRolesPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.manage_roles';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'role.read.owner', 'role.write.owner', 'role.delete.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user', 'app'];
}
