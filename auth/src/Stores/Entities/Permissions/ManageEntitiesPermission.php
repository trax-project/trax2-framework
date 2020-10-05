<?php

namespace Trax\Auth\Stores\Entities\Permissions;

use Trax\Auth\Permissions\Permission;

class ManageEntitiesPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.manage_entities';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'entity.read.owner', 'entity.write.owner', 'entity.delete.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user', 'app'];
}
