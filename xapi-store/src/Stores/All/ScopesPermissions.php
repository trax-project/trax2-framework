<?php

namespace Trax\XapiStore\Stores\All;

use Trax\Auth\Permissions\PermissionsProvider;

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
        'xapi-scope.statements-read-mine' => \Trax\XapiStore\Permissions\StatementsReadMinePermission::class,

        'xapi-scope.state' => \Trax\XapiStore\Permissions\StatePermission::class,
        'xapi-scope.profile' => \Trax\XapiStore\Permissions\ProfilePermission::class,
        'xapi-scope.define' => \Trax\XapiStore\Permissions\DefinePermission::class,
    ];
}
