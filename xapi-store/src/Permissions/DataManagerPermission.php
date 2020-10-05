<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class DataManagerPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.manage';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'statement.read.entity', 'statement.write.entity', 'statement.delete.entity',
        'state.read.entity', 'state.write.entity', 'state.delete.entity',
        'activity_profile.read.entity', 'activity_profile.write.entity', 'activity_profile.delete.entity',
        'agent_profile.read.entity', 'agent_profile.write.entity', 'agent_profile.delete.entity',
        'attachment.read.entity', 'attachment.write.entity', 'attachment.delete.entity',
        'activity.read.owner', 'activity.write.owner', 'activity.delete.owner',
        'agent.read.owner', 'agent.write.owner', 'agent.delete.owner',
        'person.read.owner', 'person.write.owner', 'person.delete.owner',
        'verb.read.owner', 'verb.write.owner', 'verb.delete.owner',

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
