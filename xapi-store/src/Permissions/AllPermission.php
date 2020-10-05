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
        'state.read.entity', 'state.write.entity', 'state.delete.entity',
        'activity_profile.read.entity', 'activity_profile.write.entity', 'activity_profile.delete.entity',
        'agent_profile.read.entity', 'agent_profile.write.entity', 'agent_profile.delete.entity',
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
