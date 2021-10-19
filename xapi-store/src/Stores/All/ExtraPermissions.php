<?php

namespace Trax\XapiStore\Stores\All;

use Trax\Auth\Permissions\PermissionsProvider;

class ExtraPermissions extends PermissionsProvider
{
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-xapi-store::permissions.xapi_extra';

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        'xapi-extra.observe' => \Trax\XapiStore\Permissions\DataObserverPermission::class,
        'xapi-extra.manage' => \Trax\XapiStore\Permissions\DataManagerPermission::class,
    ];
}
