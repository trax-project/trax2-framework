<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;
use Trax\Repo\Querying\Query;

trait ReleaseStatements
{
    /**
     * Release a batch of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @param  boolean  $allowPseudo
     * @return void
     */
    public function releaseStatements(Collection $statements, bool $allowPseudo)
    {
        if ($allowPseudo && config('trax-xapi-store.gdpr.pseudonymization', false)) {
            $this->removePendingStatusAndSave($statements);
        } else {
            $this->removePendingStatus($statements);
        }
    }

    /**
     * Release a batch of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    public function removePendingStatusAndSave(Collection $statements)
    {
        $statements->each(function ($statement) {
            $statement->pending = false;
            $statement->save();
        });
    }

    /**
     * Release a batch of statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    public function removePendingStatus(Collection $statements)
    {
        $query = new Query(['filters' => [
            'id' => ['$in' => $statements->pluck('id')->all()],
        ]]);

        $this->repository->updateByQuery($query, ['pending' => false]);
    }
}
