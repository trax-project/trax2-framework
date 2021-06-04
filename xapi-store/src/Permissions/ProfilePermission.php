<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class ProfilePermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.profile';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'activity_profile.read.owner', 'activity_profile.write.owner', 'activity_profile.delete.owner',
        'agent_profile.read.owner', 'agent_profile.write.owner', 'agent_profile.delete.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['app'];
}
