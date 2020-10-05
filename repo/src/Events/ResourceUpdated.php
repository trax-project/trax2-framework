<?php

namespace Trax\Repo\Events;

use Illuminate\Database\Eloquent\Model;

class ResourceUpdated extends ResourceEvent
{
    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = 'updated';

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
     * The original data.
     *
     * @var array
     */
    public $originalData;
    
    /**
     * The affected data.
     *
     * @var array|null
     */
    public $affectedData = null;


    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $originalData
     * @param  array  $affectedData
     * @return void
     */
    public function __construct(Model $instance, array $originalData, array $affectedData = null)
    {
        parent::__construct(get_class($instance));
        $this->resourceId = $instance->id;
        $this->resourceInstance = $instance;
        $this->originalData = $originalData;
        $this->affectedData = $affectedData;
    }
}
