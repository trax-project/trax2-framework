<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class AgentProfilePermissions extends PermissionsProvider
{
    use OwnerScope;
}
