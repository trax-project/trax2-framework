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
     * @param  bool  $validated
     * @param  object  $authority
     * @param  boolean  $allowPseudo
     * @param  boolean  $allowQueue
     * @return array
     */
    public function createStatements(array $statements, array $attachments = [], bool $validated = false, object $authority = null, bool $allowPseudo = true, bool $allowQueue = true): array
    {
        // If we are not in a relational model, and if activities recording is disabled,
        // we can skip the "pending" status and record statements directly.
        if (!config('trax-xapi-store.requests.relational', false)
            && config('trax-xapi-store.processing.disable_activities', false)
        ) {
            return $this->createStatementsWithoutPending($statements, $attachments, $validated, $authority);
        }

        // Record pending statements.
        $uuids = DB::transaction(function () use ($statements, $attachments, $validated, $authority, $allowPseudo, $allowQueue) {
            // Record the attachments.
            $this->recordAttachments($attachments);

            // Record the pending statements.
            $uuids = $this->recordPendingStatements(
                $statements,
                isset($authority) ? $authority : $this->getAccessAuthority(),
                $validated
            );

            // Dispatch the pending statements.
            // We do it inside the transaction because queues may not be used.
            $this->dispatchPendingStatements($uuids, $allowPseudo, $allowQueue);

            return $uuids;
        });

        // Return the array of UUIDs.
        return $uuids;
    }
    
    /**
     * Create statements and return their ids.
     * This method does not use a the pending status.
     *
     * @param  array  $statements
     * @param  array  $attachments
     * @param  bool  $validated
     * @param  object  $authority
     * @return array
     */
    public function createStatementsWithoutPending(array $statements, array $attachments = [], bool $validated = false, object $authority = null): array
    {
        return DB::transaction(function () use ($statements, $attachments, $validated, $authority) {
            $this->recordAttachments($attachments);
            return $this->recordStatements(
                $statements,
                isset($authority) ? $authority : $this->getAccessAuthority(),
                $validated
            );
        });
    }

    /**
     * Import statements.
     *
     * @param  array  $statements
     * @param  int  $ownerId
     * @param  int  $entityId
     * @param  boolean  $allowPseudo
     * @param  string  $authorityConfig
     * @return void
     */
    public function importStatements(array $statements, int $ownerId, int $entityId = null, bool $allowPseudo = true, string $authorityConfig = null)
    {
        TraxAuth::setContext([
            'entity_id' => $entityId,
            'owner_id' => $ownerId,
        ]);

        $this->createStatements($statements, [], false, $this->getImportAuthority($authorityConfig), $allowPseudo, false);
    }
}
