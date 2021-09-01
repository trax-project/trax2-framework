<?php

namespace Trax\XapiStore\Services\Verb;

use Illuminate\Container\Container;
use Trax\XapiStore\Services\Verb\Actions\ProcessPendingStatements;
use Trax\XapiStore\Services\Verb\Actions\BuildStatementsQuery;

class VerbService
{
    use ProcessPendingStatements, BuildStatementsQuery;

    /**
     * Repository.
     *
     * @var \Trax\XapiStore\Stores\Verbs\VerbRepository
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
        $this->repository = $container->make(\Trax\XapiStore\Stores\Verbs\VerbRepository::class);
    }
}
