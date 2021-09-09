<?php

namespace Trax\XapiStore\Services\Activity;

use Illuminate\Container\Container;
use Trax\XapiStore\Services\Activity\Actions\ProcessPendingStatements;
use Trax\XapiStore\Services\Activity\Actions\BuildStatementsQuery;

class ActivityService
{
    use ProcessPendingStatements, BuildStatementsQuery;

    /**
     * Repository.
     *
     * @var \Trax\XapiStore\Stores\Activities\ActivityRepository
     */
    protected $repository;

    /**
     * Create a new class instance.
     *
     * @param  \Illuminate\Container\Container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->repository = $container->make(\Trax\XapiStore\Stores\Activities\ActivityRepository::class);
    }
}
