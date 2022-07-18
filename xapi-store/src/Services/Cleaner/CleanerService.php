<?php

namespace Trax\XapiStore\Services\Cleaner;

use Trax\Repo\Querying\Query;
use Trax\XapiStore\Stores\Statements\StatementRepository;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Stores\Agents\AgentRepository;
use Trax\XapiStore\Stores\States\StateRepository;
use Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository;
use Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository;
use Trax\XapiStore\Stores\Attachments\AttachmentRepository;
use Trax\XapiStore\Stores\Persons\PersonRepository;
use Trax\XapiStore\Stores\Verbs\VerbRepository;
use Trax\XapiStore\Stores\ActivityTypes\ActivityTypeRepository;
use Trax\XapiStore\Stores\StatementCategories\StatementCategoryRepository;
use Trax\XapiStore\Stores\Logs\LogRepository;
use Trax\Auth\Stores\Owners\OwnerRepository;

class CleanerService
{
    /**
     * @var array
     */
    protected $repositories;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repositories = [
            app(StatementRepository::class),
            app(ActivityRepository::class),
            app(AgentRepository::class),
            app(StateRepository::class),
            app(ActivityProfileRepository::class),
            app(AgentProfileRepository::class),
            app(AttachmentRepository::class),
            app(PersonRepository::class),
            app(VerbRepository::class),
            app(ActivityTypeRepository::class),
            app(StatementCategoryRepository::class),
            app(LogRepository::class),
        ];
    }

    /**
     * Delete a store (soft delete).
     *
     * @param  int|string  $ownerId
     * @return void
     */
    public function deleteStore($ownerId): void
    {
        $owner = app(OwnerRepository::class)->find($ownerId);
        $owner->deleted_at = date('Y-m-d H:i:s');
        $owner->name = $owner->uuid;
        $owner->save();
    }

    /**
     * Delete a store (hard delete).
     *
     * @param  int|string  $ownerId
     * @return void
     */
    public function hardDeleteStore($ownerId): void
    {
        $this->clearStore($ownerId, true);
        app(OwnerRepository::class)->delete($ownerId);
    }

    /**
     * Clear all stores.
     *
     * @param  bool  $force
     * @return void
     */
    public function clearAllStores(bool $force = false): void
    {
        if (!$force) {
            $this->checkMaxDeletableStatements(new Query);
        }
        foreach ($this->repositories as $repository) {
            $repository->deleteByQuery(new Query);
        }
    }

    /**
     * Clear a store.
     *
     * @param  int|string  $ownerId
     * @param  bool  $force
     * @return void
     */
    public function clearStore($ownerId, bool $force = false): void
    {
        $query = new Query(['filters' => ['owner_id' => $ownerId]]);
        if (!$force) {
            $this->checkMaxDeletableStatements($query);
        }
        foreach ($this->repositories as $repository) {
            $repository->deleteByQuery($query);
        }
    }

    /**
     * Clear the statements of a store.
     *
     * @param  int|string  $ownerId
     * @param  bool  $force
     * @return void
     */
    public function clearStoreStatements($ownerId, array $filters = [], bool $force = false): void
    {
        $filters['owner_id'] = $ownerId;
        $query = new Query(['filters' => $filters]);
        if (!$force) {
            $this->checkMaxDeletableStatements($query);
        }
        app(StatementRepository::class)->deleteByQuery($query);
    }

    /**
     * Check if there are too many statements to delete.
     *
     * @param  \Trax\Repo\Querying\Query  $query
     * @return void
     *
     * @throws \Trax\XapiStore\Services\Cleaner\MaxDeletableException
     */
    protected function checkMaxDeletableStatements(Query $query): void
    {
        $count = app(StatementRepository::class)->count($query);
        if ($count > config('trax-lrs.deletion.statements_max', 10000)) {
            throw new MaxDeletableException();
        }
    }

    /**
     * Delete a agent (hard delete).
     *
     * @param  int  $agentId
     * @return void
     */
    public function hardDeleteAgent(int $agentId): void
    {
        $agent = app(AgentRepository::class)->findOrFail($agentId);

        // Delete the profiles.
        app(AgentProfileRepository::class)->deleteByQuery(new Query([
            'filters' => [
                'vid' => $agent->vid
            ]
        ]));

        // Delete the states.
        app(StateRepository::class)->deleteByQuery(new Query([
            'filters' => [
                'vid' => $agent->vid
            ]
        ]));

        // Delete the statements.
        if (!config('trax-xapi-store.privacy.pseudonymization', false)) {
            // We delete the statements only if the pseudonymization is disabled.
            app(StatementRepository::class)->deleteByQuery(new Query([
                'filters' =>['id' => ['$in' => $this->selectAgentStatementIdsCallback($agentId)]]
            ]));
        }

        // Delete the agent (and person).
        $person = $agent->person;
        if ($person->agents->count() == 1) {
            $person->delete();
        } else {
            $agent->delete();
        }
    }

    /**
     * Get callback for related agents filter.
     *
     * @param  int  $agentId
     * @return callable
     */
    protected function selectAgentStatementIdsCallback(int $agentId): callable
    {
        return function ($query) use ($agentId) {
            return $query->select('statement_id')->from('trax_xapi_statement_agent')
                ->where('agent_id', $agentId);
        };
    }
}
