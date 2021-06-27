<?php

namespace Trax\Auth;

use Illuminate\Support\ServiceProvider;
use Trax\Auth\Traits\RegisterPermissionProviders;

class AuthServiceProvider extends ServiceProvider
{
    use RegisterPermissionProviders;

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        'permission' => \Trax\Auth\Middleware\PermissionMiddleware::class,
        'known.access' => \Trax\Auth\Middleware\KnownAccessMiddleware::class,
        \Trax\Auth\Routing::class => \Trax\Auth\Routing::class,
        \Trax\Auth\Authentifier::class => \Trax\Auth\Authentifier::class,
        \Trax\Auth\Authorizer::class => \Trax\Auth\Authorizer::class,
        \Trax\Auth\Stores\Accesses\AccessService::class => \Trax\Auth\Stores\Accesses\AccessService::class,
        \Trax\Auth\Stores\Clients\ClientRepository::class => \Trax\Auth\Stores\Clients\ClientRepository::class,
        \Trax\Auth\Stores\Users\UserRepository::class => \Trax\Auth\Stores\Users\UserRepository::class,
        \Trax\Auth\Stores\Roles\RoleRepository::class => \Trax\Auth\Stores\Roles\RoleRepository::class,
        \Trax\Auth\Stores\Entities\EntityRepository::class => \Trax\Auth\Stores\Entities\EntityRepository::class,
        \Trax\Auth\Stores\Owners\OwnerRepository::class => \Trax\Auth\Stores\Owners\OwnerRepository::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Needed during the install.
        if ($this->app->runningInConsole()) {
            // Define migrations.
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Load translations.
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'trax-auth');

        // Define routes.
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

        // Define permissions.
        $this->registerPermissionProviders([
            'access' => config(
                'trax-auth.permissions.providers.access',
                \Trax\Auth\Stores\Accesses\AccessPermissions::class
            ),
            'client' => config(
                'trax-auth.permissions.providers.client',
                \Trax\Auth\Stores\Clients\ClientPermissions::class
            ),
            'user' => config(
                'trax-auth.permissions.providers.user',
                \Trax\Auth\Stores\Users\UserPermissions::class
            ),
            'role' => config(
                'trax-auth.permissions.providers.role',
                \Trax\Auth\Stores\Roles\RolePermissions::class
            ),
            'entity' => config(
                'trax-auth.permissions.providers.entity',
                \Trax\Auth\Stores\Entities\EntityPermissions::class
            ),
            'owner' => config(
                'trax-auth.permissions.providers.owner',
                \Trax\Auth\Stores\Owners\OwnerPermissions::class
            ),
        ]);
    }
}
