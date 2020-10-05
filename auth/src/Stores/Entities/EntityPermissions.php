<?php

namespace Trax\Auth\Stores\Entities;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Traits\OwnerScope;

class EntityPermissions extends PermissionsProvider
{
    use OwnerScope;

    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.entity';

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => ['entity.read.mine'],
        'app' => ['entity.read.mine'],
    ];

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        'entity.manage' => \Trax\Auth\Stores\Entities\Permissions\ManageEntitiesPermission::class,
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Entities\Entity  $entity
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, Entity $entity): bool
    {
        return $consumer->entity_id == $entity->id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function mineFilter(HasPermissionsContract $consumer)
    {
        return [['id' => $consumer->entity_id]];
    }
}
