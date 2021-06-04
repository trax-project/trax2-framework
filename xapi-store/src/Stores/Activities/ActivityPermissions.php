<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class ActivityPermissions extends PermissionsProvider
{
    use OwnerScope;
}
