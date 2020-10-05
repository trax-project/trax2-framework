<?php

namespace Trax\Auth\Traits;

use Trax\Repo\CrudRequest;

/**
 * This trait can be used with repo controllers when the model has an owner relation.
 * The entities repository must be set to the `entities` property by the controller.
 */
trait HasOwner
{
    /**
     * Check the owner.
     *
     * @param  \Trax\Repo\CrudRequest  $crudRequest
     * @return void
     */
    protected function checkOwner(CrudRequest $crudRequest)
    {
        // The consumer has a owner. We take the consumer owner.
        // The consumer may be null during unit testing (e.g. CRUD tests).
        $consumer = $this->authentifier->consumer();
        if (isset($consumer) && !empty($consumer->owner_id)) {
            $crudRequest->setContentField('owner_id', $consumer->owner_id);
            return;
        }

        // The owner is set by the request. We keep it.
        if (isset($crudRequest->content()['owner_id'])) {
            return;
        }

        // The owner is not set by the request.
        // And there is no default owner to assign.
        $confKey = "trax-auth.owners.default.$this->permissionsDomain";
        if (!config($confKey, false)) {
            return;
        }

        // We want to assign a default owner.
        $name = config($confKey, false);
        $owners = resolve(\Trax\Auth\Stores\Owners\OwnerRepository::class);
        $owner = $owners->addFilter(['name' => $name])->get()->first();
        if (!$owner) {
            $owner = $owners->create(['name' => $name]);
        }
        $crudRequest->setContentField('owner_id', $owner->id);
    }
}
