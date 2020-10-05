<?php

namespace Trax\XapiStore\Stores\Agents;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class AgentPermissions extends PermissionsProvider
{
    use OwnerScope;
}
