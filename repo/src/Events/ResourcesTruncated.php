<?php

namespace Trax\Repo\Events;

class ResourcesTruncated extends ResourceEvent
{
    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = 'truncated';
}
