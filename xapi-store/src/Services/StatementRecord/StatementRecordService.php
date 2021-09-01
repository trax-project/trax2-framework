<?php

namespace Trax\XapiStore\Services\StatementRecord;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Trax\Auth\TraxAuth;
use Trax\XapiStore\Services\StatementRecord\Actions\RecordPendingStatements;
use Trax\XapiStore\Services\StatementRecord\Actions\RecordAttachments;
use Trax\XapiStore\Services\StatementRecord\Actions\GetAuthority;
use Trax\XapiStore\Services\StatementRecord\Actions\DispatchPendingStatements;
use Trax\XapiStore\Services\StatementRecord\Actions\ProcessPendingStatements;

class StatementRecordService
{
    use RecordPendingStatements, RecordAttachments, GetAuthority, DispatchPendingStatements, ProcessPendingStatements;

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
     * This method does not use a DB transaction.
     *
     * @param  array  $statements
     * @param  array  $attachments
     * @return array
     */
    public function createStatements(array $statements, array $attachments = []): array
    {
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
