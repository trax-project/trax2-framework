<?php

namespace Trax\XapiStore\Stores\Attachments;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\XapiStore\Traits\ContextScopes;

class AttachmentPermissions extends PermissionsProvider
{
    use ContextScopes;
}
