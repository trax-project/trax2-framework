<?php

namespace Trax\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\AuthorizationException;
use Trax\Auth\Contracts\PermissionsProviderContract;

class Authorizer
{
    /**
     * The authentication services.
     *
     * @var \Trax\Auth\Authentifier
     */
    protected $auth;

    /**
     * The registered permission providers.
     *
     * @var array
     */
    protected $permissionProviders = [];

    /**
     * The registry where are stored all the permission instances.
     *
     * @var \Trax\Auth\PermissionsRegistry
     */
    protected $permissionsRegistry;

    /**
     * Create a the auth services.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->auth = $app->make(Authentifier::class);
        $this->permissionsRegistry = new PermissionsRegistry();
    }

    /**
     * Register the permissions of a domain giving its permissions provider.
     *
     * @param  string  $domain
     * @param  \Trax\Auth\Contracts\PermissionsProviderContract  $provider
     * @return void
     */
    public function registerPermissions(string $domain, PermissionsProviderContract $provider): void
    {
        $provider->setRegistry($this->permissionsRegistry);
        $this->permissionProviders[$domain] = $provider;
        $this->permissionsRegistry->register($provider->permissionClasses());
    }

    /**
     * Get all available permissions.
     *
     * @param string|null  $type
     * @return array
     */
    public function permissions(string $type = null): array
    {
        return collect($this->permissionProviders)->map(function ($provider) use ($type) {
            $permissions = $provider->assignablePermissions($type);
            if (empty($permissions)) {
                return false;
            }
            return [
                'provider' => [
                    'name' => $provider->name(),
                    'description' => $provider->description(),
                ],
                'permissions' => $permissions,
            ];
        })->filter()->all();
    }

    /**
     * Check a permission.
     *
     * @param string  $permission  domain.operation(.scope)
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return bool
     */
    public function can(string $permission, Model $resource = null): bool
    {
        if ($this->auth->consumer()->isAdmin()) {
            return true;
        }
        $domain = explode('.', $permission)[0];
        if (!isset($this->permissionProviders[$domain])) {
            return false;
        }
        $provider = $this->permissionProviders[$domain];
        return $provider->check($permission, $this->auth->consumer(), $resource);
    }

    /**
     * Check a permission and throw an exception when the permission is not granted.
     *
     * @param string  $permission  domain.operation(.scope)
     * @param \Illuminate\Database\Eloquent\Model  $resource
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function must(string $permission, Model $resource = null): void
    {
        if (!$this->can($permission, $resource)) {
            throw new AuthorizationException("Forbidden: [$permission] permission not granted.");
        }
    }

    /**
     * Get a filter that should be applied to all requests in order to return only allowed items.
     * Return null when no resource should be returned.
     *
     * @param string  $domain
     * @return array|null
     */
    public function scopeFilter(string $domain)
    {
        if (!isset($this->permissionProviders[$domain])) {
            return null;
        }
        return $this->permissionProviders[$domain]->scopeFilter($this->auth->consumer(), $domain);
    }
}
