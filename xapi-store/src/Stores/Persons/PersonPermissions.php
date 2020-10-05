<?php

namespace Trax\XapiStore\Stores\Persons;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class PersonPermissions extends PermissionsProvider
{
    use OwnerScope;
}
