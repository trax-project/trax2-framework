<?php

namespace Trax\Auth\Stores\Clients\Permissions;

use Trax\Auth\Permissions\Permission;

class ManageClientsPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.manage_clients';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'client.read.owner', 'client.write.owner', 'client.delete.owner',
        'access.read.owner', 'access.write.owner', 'access.delete.owner',
        'entity.read.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user'];
}
