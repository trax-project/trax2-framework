<?php

namespace Trax\Auth\Stores\Accesses;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Stores\Accesses\Access;

class AccessPermissions extends PermissionsProvider
{
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.access';

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => [],
        'app' => ['access.read.mine'],
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Accesses\Access  $access
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, Access $access): bool
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return false;
        }

        return $consumer->id == $access->id;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function mineFilter(HasPermissionsContract $consumer)
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return null;
        }

        return [['id' => $consumer->id]];
    }

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Accesses\Access  $access
     * @return bool
     */
    protected function owner(HasPermissionsContract $consumer, Access $access): bool
    {
        // When a consumer is not associated with an owner, it can access all data.
        return empty($consumer->owner_id) || $access->owner_id == $consumer->owner_id;
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

        $clients = resolve(\Trax\Auth\Stores\Clients\ClientRepository::class);
        $clientIds = $clients->addFilter(['owner_id' => $consumer->owner_id])->get()->pluck('id')->toArray();
        return [['client_id' => ['$in' => $clientIds]]];
    }
}
