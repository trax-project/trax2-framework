<?php

namespace Trax\Repo\Events;

class ResourcesDeleted extends ResourceEvent
{
    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = 'bulk deleted';

    /**
     * The ID.
     *
     * @var array
     */
    public $resourceIds;


    /**
     * Create a new event instance.
     *
     * @param  string  $class
     * @param  array  $ids
     * @return void
     */
    public function __construct(string $class, array $ids)
    {
        parent::__construct($class);
        $this->resourceIds = $ids;
    }
}
