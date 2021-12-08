<?php

namespace Trax\Auth\Traits;

use Trax\Repo\CrudRequest;

/**
 * This trait can be used with repo controllers when the model has an entity relation.
 */
trait HasEntity
{
    /**
     * Check the entity.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @return void
     */
    protected function checkEntity(CrudRequest $crudRequest)
    {
        // The consumer has an entity. We take the consumer entity.
        // The consumer may be null during unit testing (e.g. CRUD tests).
        $consumer = $this->authentifier->consumer();
        if (isset($consumer) && !empty($consumer->entity_id)) {
            $crudRequest->setContentField('entity_id', $consumer->entity_id);
            return;
        }

        // The entity is set by the request. We keep it.
        // The entity is not set by the request. It will be null.
    }
}
