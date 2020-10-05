<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class DefinePermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.define';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'activity.read.owner', 'activity.write.owner',  // Write permission checked to update definitions
        'agent.read.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['app'];
}
