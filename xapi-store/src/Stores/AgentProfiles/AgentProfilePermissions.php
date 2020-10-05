<?php

namespace Trax\XapiStore\Stores\AgentProfiles;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\XapiStore\Traits\ContextScopes;

class AgentProfilePermissions extends PermissionsProvider
{
    use ContextScopes;
}
