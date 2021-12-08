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

        // Entity scope.
        'statement.read.entity', 'statement.write.entity', 'statement.delete.entity',
        'attachment.read.entity', 'attachment.write.entity', 'attachment.delete.entity',
        'log.read.entity', 'log.write.entity', 'log.delete.entity',

        // Owner scope.
        'state.read.owner',
        'activity_profile.read.owner',
        'agent_profile.read.owner',
        'activity.read.owner',
        'agent.read.owner',
        'person.read.owner',
        'verb.read.owner',
        'activity_type.read.owner',
        'statement_category.read.owner',

        // For filtering.
        'entity.read.mine',
        'client.read.entity',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user', 'app'];
}
