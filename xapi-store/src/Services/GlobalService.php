<?php

namespace Trax\XapiStore\Services;

use Illuminate\Container\Container;
use Trax\Repo\Querying\Query;

class GlobalService
{
    /**
     * @var \Trax\XapiStore\Stores\Statements\StatementService
     */
    protected $statements;

    /**
     * @var \Trax\XapiStore\Stores\Activities\ActivityRepository
     */
    protected $activities;

    /**
     * @var \Trax\XapiStore\Stores\Agents\AgentRepository
     */
    protected $agents;

    /**
     * @var \Trax\XapiStore\Stores\States\StateRepository
     */
    protected $states;

    /**
     * @var \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository
     */
    protected $activityProfiles;

    /**
     * @var \Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository
     */
    protected $agentProfiles;

    /**
     * @var \Trax\XapiStore\Stores\Attachments\AttachmentRepository
     */
    protected $attachments;

    /**
     * @var \Trax\XapiStore\Stores\Persons\PersonRepository
     */
    protected $persons;

    /**
     * @var \Trax\XapiStore\Stores\Verbs\VerbRepository
     */
    protected $verbs;


    /**
     * Create a new class instance.
     *
     * @param  \Illuminate\Container\Container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->statements = $container->make(\Trax\XapiStore\Stores\Statements\StatementService::class);
        $this->activities = $container->make(\Trax\XapiStore\Stores\Activities\ActivityRepository::class);
        $this->agents = $container->make(\Trax\XapiStore\Stores\Agents\AgentRepository::class);
        $this->states = $container->make(\Trax\XapiStore\Stores\States\StateRepository::class);
        $this->activityProfiles = $container->make(\Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository::class);
        $this->agentProfiles = $container->make(\Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository::class);
        $this->attachments = $container->make(\Trax\XapiStore\Stores\Attachments\AttachmentRepository::class);
        $this->persons = $container->make(\Trax\XapiStore\Stores\Persons\PersonRepository::class);
        $this->verbs = $container->make(\Trax\XapiStore\Stores\Verbs\VerbRepository::class);
    }

    /**
     * Clear a store.
     *
     * @param  int|string  $ownerId
     * @return void
     */
    public function clearStore($ownerId): void
    {
        $query = new Query(['filters' => ['owner_id' => $ownerId]]);
        $this->statements->deleteByQuery($query);
        $this->activities->deleteByQuery($query);
        $this->agents->deleteByQuery($query);
        $this->states->deleteByQuery($query);
        $this->activityProfiles->deleteByQuery($query);
        $this->agentProfiles->deleteByQuery($query);
        $this->attachments->deleteByQuery($query);
        $this->persons->deleteByQuery($query);
        $this->verbs->deleteByQuery($query);
    }

    /**
     * Clear ALL the repositories.
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->statements->truncate();
        $this->activities->truncate();
        $this->agents->truncate();
        $this->states->truncate();
        $this->activityProfiles->truncate();
        $this->agentProfiles->truncate();
        $this->attachments->truncate();
        $this->persons->truncate();
        $this->verbs->truncate();
    }
}
