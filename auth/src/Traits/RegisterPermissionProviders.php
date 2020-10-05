<?php

namespace Trax\Auth\Traits;

trait RegisterPermissionProviders
{
    /**
     * Register permission providers.
     *
     * @param array  $permissionProviders
     * @return void
     */
    public function registerPermissionProviders(array $permissionProviders = []): void
    {
        $providers = isset($this->permissionProviders) ? $this->permissionProviders : [];
        $providers = array_merge($providers, $permissionProviders);

        collect($providers)->each(function ($providerClass, $domain) {
            $this->app->make(\Trax\Auth\Authorizer::class)->registerPermissions($domain, new $providerClass);
        });
    }
}
