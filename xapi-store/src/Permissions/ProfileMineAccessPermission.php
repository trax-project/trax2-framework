<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class ProfileMineAccessPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.profile_mine_access';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'activity_profile.read.access', 'activity_profile.write.access', 'activity_profile.delete.access',
        'agent_profile.read.access', 'agent_profile.write.access', 'agent_profile.delete.access',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['app'];
}
