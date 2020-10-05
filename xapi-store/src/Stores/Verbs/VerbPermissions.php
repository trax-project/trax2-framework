<?php

namespace Trax\XapiStore\Stores\Verbs;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class VerbPermissions extends PermissionsProvider
{
    use OwnerScope;
}
