<?php

namespace Trax\XapiStore\Services;

use Illuminate\Container\Container;
use Trax\Repo\Querying\Query;
use Trax\XapiStore\XapiLogging\XapiLogger;

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
     * @var \Trax\XapiStore\Stores\Agents\AgentService
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
        $this->agents = $container->make(\Trax\XapiStore\Stores\Agents\AgentService::class);
        $this->states = $container->make(\Trax\XapiStore\Stores\States\StateRepository::class);
        $this->activityProfiles = $container->make(\Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository::class);
        $this->agentProfiles = $container->make(\Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository::class);
        $this->attachments = $container->make(\Trax\XapiStore\Stores\Attachments\AttachmentRepository::class);
        $this->persons = $container->make(\Trax\XapiStore\Stores\Persons\PersonRepository::class);
        $this->verbs = $container->make(\Trax\XapiStore\Stores\Verbs\VerbRepository::class);
        $this->owners = $container->make(\Trax\Auth\Stores\Owners\OwnerRepository::class);
    }

    /**
     * Clear all the stores.
     *
     * @return void
     */
    public function clearStores(): void
    {
        // Truncate can't be used because it does not accept foreign keys.
        $query = new Query();
        $this->statements->deleteByQuery($query);
        $this->activities->deleteByQuery($query);
        $this->agents->deleteByQuery($query);
        $this->states->deleteByQuery($query);
        $this->activityProfiles->deleteByQuery($query);
        $this->agentProfiles->deleteByQuery($query);
        $this->attachments->deleteByQuery($query);
        $this->persons->deleteByQuery($query);
        $this->verbs->deleteByQuery($query);
        XapiLogger::clear();
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
        XapiLogger::clear($ownerId);
    }

    /**
     * Delete a store.
     *
     * @param  int|string  $ownerId
     * @return void
     */
    public function deleteStore($ownerId): void
    {
        $this->clearStore($ownerId);
        $this->owners->delete($ownerId);
    }
}
