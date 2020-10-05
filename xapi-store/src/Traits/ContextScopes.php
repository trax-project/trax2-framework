<?php

namespace Trax\XapiStore\Traits;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Traits\OwnerScope;

trait ContextScopes
{
    use OwnerScope;
    
    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return bool
     */
    protected function access(HasPermissionsContract $consumer, Model $resource): bool
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return false;
        }

        // When the consumer is an access.
        return $consumer->id == $resource->access_id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function accessFilter(HasPermissionsContract $consumer)
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return null;
        }

        return [['access_id' => $consumer->id]];
    }
    
    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return bool
     */
    protected function client(HasPermissionsContract $consumer, Model $resource): bool
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return false;
        }

        // When the consumer is an access.
        return $consumer->client_id == $resource->client_id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function clientFilter(HasPermissionsContract $consumer)
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return null;
        }

        return [['client_id' => $consumer->client_id]];
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
