<?php

namespace Trax\XapiStore\Services\StatementRecord\Actions;

use Illuminate\Support\Collection;

trait VoidStatements
{
    /**
     * Process voiding statements.
     *
     * @param  \Illuminate\Support\Collection  $statements
     * @return void
     */
    public function processVoidingStatements(Collection $statements): void
    {
        $statements->where('data.verb.id', 'http://adlnet.gov/expapi/verbs/voided')->each(function ($voiding) {

            $target = $this->repository->addFilter([
                'voided' => false,
                'uuid' => $voiding->data->object->id,
                'owner_id' => $voiding->owner_id
            ])->get()->first();
            
            if ($target) {
                $target->voided = true;
                $target->save();
            }
        });
    }
}
