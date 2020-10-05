<?php

namespace Trax\Repo\Events;

use Illuminate\Database\Eloquent\Model;

class ResourceCreated extends ResourceEvent
{
    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = 'created';

    /**
     * The ID.
     *
     * @var integer
     */
    public $resourceId;
    
    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $resourceInstance;
    

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return void
     */
    public function __construct(Model $instance)
    {
        parent::__construct(get_class($instance));
        $this->resourceId = $instance->id;
        $this->resourceInstance = $instance;
    }
}
