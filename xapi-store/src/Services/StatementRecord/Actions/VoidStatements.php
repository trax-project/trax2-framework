<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Trax\Auth\TraxAuth;
use Trax\Repo\Querying\Query;

trait VoidStatements
{
    /**
     * Void a batch of statements.
     *
     * @param  array  $statementIds
     * @return void
     */
    public function voidStatements(array $statementIds): void
    {
        if (empty($statementIds)) {
            return;
        }

        $query = new Query(['filters' => [
            'voided' => false,
            'uuid' => ['$in' => $statementIds],
            'owner_id' => TraxAuth::context('owner_id')
        ]]);

        $this->repository->updateByQuery($query, ['voided' => true]);
    }
}
