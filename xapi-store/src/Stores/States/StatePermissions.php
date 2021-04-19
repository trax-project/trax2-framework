<?php

namespace Trax\XapiStore\Stores\States;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class StatePermissions extends PermissionsProvider
{
    use OwnerScope;
}
