<?php

namespace Trax\XapiStore\Stores\ActivityProfiles;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\XapiStore\Traits\ContextScopes;

class ActivityProfilePermissions extends PermissionsProvider
{
    use ContextScopes;
}
