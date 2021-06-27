<?php

use Trax\Auth\TraxRouting;
use Trax\Auth\Stores\Clients\ClientController;
use Trax\Auth\Stores\Accesses\AccessController;
use Trax\Auth\Stores\Users\UserController;
use Trax\Auth\Stores\Roles\RoleController;
use Trax\Auth\Stores\Entities\EntityController;
use Trax\Auth\Stores\Owners\OwnerController;

if (config('trax-auth.services.clients', true)) {
    //
    TraxRouting::userCrudRoutes(
        'trax/api',
        'clients',
        config('trax-auth.controllers.client', ClientController::class),
        config('trax-auth.routes.client', []),
    );
    TraxRouting::userCrudRoutes(
        'trax/api',
        'accesses',
        config('trax-auth.controllers.access', AccessController::class),
        config('trax-auth.routes.access', []),
    );
}

if (config('trax-auth.services.users', false)) {
    // Keep it before the user API!

    TraxRouting::userGetRoute(
        'trax/api',
        'users/me',
        config('trax-auth.controllers.user', UserController::class) . '@showMe'
    );
    TraxRouting::userPutRoute(
        'trax/api',
        'users/me',
        config('trax-auth.controllers.user', UserController::class) . '@updateMe'
    );
    TraxRouting::userPostRoute(
        'trax/api',
        'users/me/password',
        config('trax-auth.controllers.user', UserController::class) . '@changeMyPassword'
    );

    TraxRouting::userCrudRoutes(
        'trax/api',
        'users',
        config('trax-auth.controllers.user', UserController::class),
        config('trax-auth.routes.user', []),
    );

    TraxRouting::authApiRoutes();
}

if (config('trax-auth.services.roles', false)) {
    TraxRouting::userCrudRoutes(
        'trax/api',
        'roles',
        config('trax-auth.controllers.role', RoleController::class),
        config('trax-auth.routes.role', []),
    );
}

if (config('trax-auth.services.entities', false)) {
    TraxRouting::userCrudRoutes(
        'trax/api',
        'entities',
        config('trax-auth.controllers.entity', EntityController::class),
        config('trax-auth.routes.entity', []),
    );
}

if (config('trax-auth.services.owners', false)) {
    TraxRouting::userCrudRoutes(
        'trax/api',
        'owners',
        config('trax-auth.controllers.owner', OwnerController::class),
        config('trax-auth.routes.owner', []),
    );
}
