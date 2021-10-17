<?php

namespace Trax\XapiStore\Stores\States;

use Illuminate\Database\Eloquent\Model;
use Trax\Auth\Contracts\HasPermissionsContract;
use Trax\Auth\Permissions\PermissionsProvider;
use Trax\Auth\Traits\OwnerScope;

class StatePermissions extends PermissionsProvider
{
    use OwnerScope;

    /**
     * The default capabilities for each consumer type: you MUST override this property.
     *
     * @var array
     */
    protected $defaultCapabilities = [
        'user' => ['state.read.mine'],
        'app' => [],
    ];

    /**
     * Check if a resource belongs to the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return bool
     */
    protected function mine(HasPermissionsContract $consumer, Model $resource): bool
    {
        // We currently don't need this method because `mine` scope is used
        // only to limit the returned resources.
        // It is never used to control a single resource.
        return false;
    }

    /**
     * Get a filter for requests to fit with the consumer scope.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return array|null
     */
    public function mineFilter(HasPermissionsContract $consumer)
    {
        // No token, no permission.
        $tokenServiceClass = config('trax-xapi-store.privacy.token_service', false);
        if (!$tokenServiceClass || !$agents = app($tokenServiceClass)->currentXapiAgents()) {
            return null;
        }

        return [['agents' => $agents]];
    }
}
