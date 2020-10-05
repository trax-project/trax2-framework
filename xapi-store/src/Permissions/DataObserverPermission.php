<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class DataObserverPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.observe';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'statement.read.entity',
        'state.read.entity',
        'activity_profile.read.entity',
        'agent_profile.read.entity',
        'attachment.read.entity',
        'activity.read.owner',
        'agent.read.owner',
        'person.read.owner',
        'verb.read.owner',

        'entity.read.owner',    // For filtering
        'client.read.owner',    // For filtering
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user', 'app'];
}
