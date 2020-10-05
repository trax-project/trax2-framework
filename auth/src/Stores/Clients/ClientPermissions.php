<?php

namespace Trax\Auth\Stores\Clients;

use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Traits\OwnerScope;

class ClientPermissions extends PermissionsProvider
{
    use OwnerScope;
    
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey = 'trax-auth::permissions.client';

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => [],
        'app' => ['client.read.mine'],
    ];

    /**
     * The permission classes: you MUST override this property.
     *
     * @var array
     */
    protected $permissionClasses = [
        'client.manage' => \Trax\Auth\Stores\Clients\Permissions\ManageClientsPermission::class,
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Trax\Auth\Stores\Clients\Client  $client
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, Client $client): bool
    {
        // No sense for users.
        if ($consumer->consumerType() == 'user') {
            return false;
        }

        // When the consumer is an access.
        return $consumer->client_id == $client->id;
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

        return [['id' => $consumer->client_id]];
    }
}
