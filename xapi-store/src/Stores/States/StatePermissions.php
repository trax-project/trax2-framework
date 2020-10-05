<?php

namespace Trax\XapiStore\Stores\States;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\XapiStore\Traits\ContextScopes;

class StatePermissions extends PermissionsProvider
{
    use ContextScopes;
}
