<?php

namespace Trax\XapiStore\Services\StatementRecord\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Trax\XapiStore\Services\StatementRecord\Actions\DispatchPendingStatements;
use Trax\Repo\Querying\Query;
use Trax\Auth\TraxAuth;

class DispatchPendingStatementsJob implements ShouldQueue, ShouldBeUnique
{
    use DispatchPendingStatements, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The owner ID.
     *
     * @var int
     */
    protected $ownerId = null;

    /**
     * Create a new job instance.
     *
     * @param  int  $ownerId
     * @return void
     */
    public function __construct(int $ownerId = null)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->ownerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        TraxAuth::setContext([
            'owner_id' => $this->ownerId
        ]);

        while (1) {
            $statements = app(\Trax\XapiStore\Stores\Statements\StatementRepository::class)->get(new Query([
                'filters' => [
                    'pending' => true,
                    'owner_id' => $this->ownerId
                ],
                'sort' => ['id'],
                'limit' => 100
            ]));
            if ($statements->isEmpty()) {
                return;
            }
            $this->processStatementsBatch($statements);
        }
    }
}
