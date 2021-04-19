<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class ActivityProfilePermissions extends PermissionsProvider
{
    use OwnerScope;
}
