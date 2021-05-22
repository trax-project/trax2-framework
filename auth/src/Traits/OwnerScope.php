<?php

namespace Trax\Auth\Traits;

use Trax\Auth\Contracts\HasPermissionsContract;
use Illuminate\Database\Eloquent\Model;

trait OwnerScope
{
    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return bool
     */
    protected function owner(HasPermissionsContract $consumer, Model $resource): bool
    {
        // When a consumer is not associated with an owner, it can access all data.
        return empty($consumer->owner_id) || $resource->owner_id == $consumer->owner_id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function ownerFilter(HasPermissionsContract $consumer)
    {
        // When a consumer is not associated with an owner, it can access all data.
        if (empty($consumer->owner_id)) {
            return [[]];
        }

        return [['owner_id' => $consumer->owner_id]];
    }
    
    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return bool
     */
    protected function entity(HasPermissionsContract $consumer, Model $resource): bool
    {
        // When a consumer is not associated with an entity, it works with the owner data.
        if (is_null($consumer->entity_id)) {
            return $this->owner($consumer, $resource);
        }

        // When a consumer is associated with an entity, it works only with this entity data.
        return $resource->entity_id == $consumer->entity_id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function entityFilter(HasPermissionsContract $consumer)
    {
        // When a consumer is not associated with an entity, it works with the owner data.
        if (is_null($consumer->entity_id)) {
            return $this->ownerFilter($consumer);
        }

        // When a consumer is associated with an entity, it works only with this entity data.
        return [['entity_id' => $consumer->entity_id]];
    }
}
