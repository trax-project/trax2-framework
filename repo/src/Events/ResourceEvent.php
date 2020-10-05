<?php

namespace Trax\Repo\Events;

use Trax\Core\Contracts\Event;
use Illuminate\Queue\SerializesModels;

abstract class ResourceEvent implements Event
{
    use SerializesModels;

    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = '';    // Should be overriden.

    /**
     * The model class.
     *
     * @var string
     */
    public $resourceClass;


    /**
     * Create a new event instance.
     *
     * @param  string  $class
     * @return void
     */
    public function __construct(string $class)
    {
        $this->resourceClass = $class;
    }

    /**
     * Return a log message.
     *
     * @return string
     */
    public function logMessage(): string
    {
        return $this->resourceAction . ' ' . class_basename($this->resourceClass);
    }
}
