<?php

namespace Trax\XapiStore\Stores\Logs;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class LogPermissions extends PermissionsProvider
{
    use OwnerScope;
}
