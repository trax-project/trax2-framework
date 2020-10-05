<?php

namespace Trax\Repo\Events;

use Trax\Repo\Querying\Query;

class ResourcesDeletedByQuery extends ResourceEvent
{
    /**
     * The event action.
     *
     * @var string
     */
    public $resourceAction = 'deleted by query';

    /**
     * The query.
     *
     * @var \Trax\Repo\Querying\Query
     */
    public $query;


    /**
     * Create a new event instance.
     *
     * @param  string  $class
     * @param \Trax\Repo\Querying\Query  $query
     * @return void
     */
    public function __construct(string $class, Query $query)
    {
        parent::__construct($class);
        $this->query = $query;
    }
}
