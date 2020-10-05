<?php

namespace Trax\XapiStore\Permissions;

use Trax\Auth\Permissions\Permission;

class StatementsReadMineAccessPermission extends Permission
{
    /**
     * The permission lang key: you MUST override this property.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.statements_read_mine_access';

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [
        'statement.read.access',
    ];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['app'];
}
