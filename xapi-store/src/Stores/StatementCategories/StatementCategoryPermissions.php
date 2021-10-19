<?php

namespace Trax\XapiStore\Stores\StatementCategories;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class StatementCategoryPermissions extends PermissionsProvider
{
    use OwnerScope;
}
