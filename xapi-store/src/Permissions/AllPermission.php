<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class AllPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.all';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'statement.read.entity', 'statement.write.entity',
        'state.read.owner', 'state.write.owner', 'state.delete.owner',
        'activity_profile.read.owner', 'activity_profile.write.owner', 'activity_profile.delete.owner',
        'agent_profile.read.owner', 'agent_profile.write.owner', 'agent_profile.delete.owner',
        'activity.read.owner', 'activity.write.owner',  // Write permission checked by the Statement service.
        'agent.read.owner',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['app'];
}
