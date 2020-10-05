<?php

namespace Trax\Repo\Events;

class ResourceDeleted extends ResourceEvent
{
    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = 'deleted';

    /**
     * The ID.
     *
     * @var integer
     */
    public $resourceId;
    
    /**
     * The original data.
     *
     * @var array
     */
    public $originalData;


    /**
     * Create a new event instance.
     *
     * @param  string  $class
     * @param  array  $originalData
     * @return void
     */
    public function __construct(string $class, array $originalData)
    {
        parent::__construct($class);
        $this->resourceId = $originalData['id'];
        $this->originalData = $originalData;
    }
}
