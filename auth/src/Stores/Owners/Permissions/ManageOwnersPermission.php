<?php

namespace Trax\Auth\Stores\Owners\Permissions;

use Trax\Auth\Permissions\Permission;

class ManageOwnersPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.manage_owners';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'owner.read.all', 'owner.write.all', 'owner.delete.all',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user'];
}
