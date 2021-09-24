<?php

namespace Trax\XapiStore\Services\Cleaner;

use Trax\Repo\Querying\Query;

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
            app(\Trax\XapiStore\Stores\Statements\StatementRepository::class),
            app(\Trax\XapiStore\Stores\Activities\ActivityRepository::class),
            app(\Trax\XapiStore\Stores\Agents\AgentRepository::class),
            app(\Trax\XapiStore\Stores\States\StateRepository::class),
            app(\Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository::class),
            app(\Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository::class),
            app(\Trax\XapiStore\Stores\Attachments\AttachmentRepository::class),
            app(\Trax\XapiStore\Stores\Persons\PersonRepository::class),
            app(\Trax\XapiStore\Stores\Verbs\VerbRepository::class),
            app(\Trax\XapiStore\Stores\ActivityTypes\ActivityTypeRepository::class),
            app(\Trax\XapiStore\Stores\StatementCategories\StatementCategoryRepository::class),
            app(\Trax\XapiStore\Stores\Logs\LogRepository::class),
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
        $owner = app(\Trax\Auth\Stores\Owners\OwnerRepository::class)->find($ownerId);
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
        app(\Trax\Auth\Stores\Owners\OwnerRepository::class)->delete($ownerId);
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
        app(\Trax\XapiStore\Stores\Statements\StatementRepository::class)->deleteByQuery($query);
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
        $count = app(\Trax\XapiStore\Stores\Statements\StatementRepository::class)->count($query);
        if ($count > config('trax-lrs.deletion.statements_max', 10000)) {
            throw new MaxDeletableException();
        }
    }
}
