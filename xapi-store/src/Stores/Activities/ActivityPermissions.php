<?php

namespace Trax\XapiStore\Stores\Activities;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;
use Trax\Auth\Contracts\HasPermissionsContract;

class ActivityPermissions extends PermissionsProvider
{
    use OwnerScope;
}
