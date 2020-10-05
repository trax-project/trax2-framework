<?php

namespace Trax\XapiStore\Stores\Statements;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\XapiStore\Traits\ContextScopes;

class StatementPermissions extends PermissionsProvider
{
    use ContextScopes;
}
