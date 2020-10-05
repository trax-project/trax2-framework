<?php

namespace Trax\XapiStore\Stores\All;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\XapiStore\Stores\Statements\Statement;

class ScopesPermissions extends PermissionsProvider
{
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.xapi_scopes';

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        
        'xapi-scope.all' => \Trax\XapiStore\Permissions\AllPermission::class,
        'xapi-scope.all-read' => \Trax\XapiStore\Permissions\AllReadPermission::class,

        'xapi-scope.statements-write' => \Trax\XapiStore\Permissions\StatementsWritePermission::class,
        'xapi-scope.statements-read' => \Trax\XapiStore\Permissions\StatementsReadPermission::class,
        'xapi-scope.statements-read-mine-client' => \Trax\XapiStore\Permissions\StatementsReadMineClientPermission::class,
        'xapi-scope.statements-read-mine-access' => \Trax\XapiStore\Permissions\StatementsReadMineAccessPermission::class,

        'xapi-scope.state-mine-client' => \Trax\XapiStore\Permissions\StateMineClientPermission::class,
        'xapi-scope.state-mine-access' => \Trax\XapiStore\Permissions\StateMineAccessPermission::class,

        'xapi-scope.profile-mine-client' => \Trax\XapiStore\Permissions\ProfileMineClientPermission::class,
        'xapi-scope.profile-mine-access' => \Trax\XapiStore\Permissions\ProfileMineAccessPermission::class,

        'xapi-scope.define' => \Trax\XapiStore\Permissions\DefinePermission::class,
    ];
}
