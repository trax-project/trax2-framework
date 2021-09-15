<?php

namespace Trax\XapiStore\Stores\ActivityTypes;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class ActivityTypePermissions extends PermissionsProvider
{
    use OwnerScope;
}
