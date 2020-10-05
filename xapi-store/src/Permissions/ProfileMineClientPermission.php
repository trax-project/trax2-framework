<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class ProfileMineClientPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.profile_mine_client';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'activity_profile.read.client', 'activity_profile.write.client', 'activity_profile.delete.client',
        'agent_profile.read.client', 'agent_profile.write.client', 'agent_profile.delete.client',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['app'];
}
