<?php

namespace Trax\XapiStore\Services\StatementRecord;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Services\StatementRecord\Actions\RecordStatements;
use Trax\XapiStore\Services\StatementRecord\Actions\RecordAttachments;
use Trax\XapiStore\Services\StatementRecord\Actions\GetAuthority;
use Trax\XapiStore\Services\StatementRecord\Actions\DispatchPendingStatements;
use Trax\XapiStore\Services\StatementRecord\Actions\ProcessPendingStatements;

class StatementRecordService
{
    use RecordStatements, RecordAttachments, GetAuthority, DispatchPendingStatements, ProcessPendingStatements;

    /**
     * Repository.
     *
     * @var \Trax\XapiStore\Stores\Statements\StatementRepository
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
        $this->repository = $container->make(\Trax\XapiStore\Stores\Statements\StatementRepository::class);
    }
    
    /**
     * Create statements and return their ids.
     *
     * @param  array  $statements
     * @param  array  $attachments
     * @return array
     */
    public function createStatements(array $statements, array $attachments = []): array
    {
        // If we are not in a relational model, and if activities recording is disabled,
        // we can skip the "pending" status and record statements directly.
        if (!config('trax-xapi-store.requests.relational', false)
            && config('trax-xapi-store.processing.disable_activities', false)
        ) {
            return $this->createStatementsWithoutPending($statements, $attachments);
        }

        // Record pending statements.
        $uuids = DB::transaction(function () use ($statements, $attachments) {
            $this->recordAttachments($attachments);
            return $this->recordPendingStatements($statements, $this->getAccessAuthority());
        });
        
        // Dispatch the pending statements.
        $this->dispatchPendingStatements($uuids);

        // Return the array of UUIDs.
        return $uuids;
    }
    
    /**
     * Create statements and return their ids.
     * This method does not use a the pending status.
     *
     * @param  array  $statements
     * @param  array  $attachments
     * @return array
     */
    public function createStatementsWithoutPending(array $statements, array $attachments = []): array
    {
        return DB::transaction(function () use ($statements, $attachments) {
            $this->recordAttachments($attachments);
            return $this->recordStatements($statements, $this->getAccessAuthority());
        });
    }

    /**
     * Import statements.
     *
     * @param  array  $statements
     * @param  int  $ownerId
     * @param  int  $entityId
     * @param  boolean  $allowPseudonymization
     * @return void
     */
    public function importStatements(array $statements, int $ownerId, int $entityId = null, bool $allowPseudonymization = true)
    {
        // Set the import context.
        TraxAuth::setContext([
            'entity_id' => $entityId,
            'owner_id' => $ownerId,
        ]);

        // Record pending statements.
        $uuids = $this->recordPendingStatements($statements, $this->getImportAuthority());

        // Dispatch the pending statements.
        $this->dispatchPendingStatements($uuids, $allowPseudonymization);
    }
}
