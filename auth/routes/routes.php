<?php

use Illuminate\Support\Facades\Route;
use Trax\Auth\TraxAuth;
use Trax\Auth\Stores\Clients\ClientController;
use Trax\Auth\Stores\Accesses\AccessController;
use Trax\Auth\Stores\Users\UserController;
use Trax\Auth\Stores\Roles\RoleController;
use Trax\Auth\Stores\Entities\EntityController;

if (config('trax-auth.services.clients', true)) {
    //
    TraxAuth::crudRoutes(
        'trax/api/{source}/clients',
        config('trax-auth.controllers.client', ClientController::class)
    );
    TraxAuth::crudRoutes(
        'trax/api/{source}/accesses',
        config('trax-auth.controllers.access', AccessController::class)
    );
}

if (config('trax-auth.services.users', false)) {
    // Keep it before the user API!
    Route::middleware(TraxAuth::userMiddleware())->group(function () {
        Route::get(
            'trax/api/{source}/users/me',
            config('trax-auth.controllers.user', UserController::class) . '@showMe'
        );
        Route::put(
            'trax/api/{source}/users/me',
            config('trax-auth.controllers.user', UserController::class) . '@updateMe'
        );
        Route::post(
            'trax/api/{source}/users/me/password',
            config('trax-auth.controllers.user', UserController::class) . '@changeMyPassword'
        );
    });

    TraxAuth::crudRoutes(
        'trax/api/{source}/users',
        config('trax-auth.controllers.user', UserController::class)
    );

    TraxAuth::authApiRoutes();
}

if (config('trax-auth.services.roles', false)) {
    TraxAuth::crudRoutes(
        'trax/api/{source}/roles',
        config('trax-auth.controllers.role', RoleController::class)
    );
}

if (config('trax-auth.services.entities', false)) {
    TraxAuth::crudRoutes(
        'trax/api/{source}/entities',
        config('trax-auth.controllers.entity', EntityController::class)
    );
}

if (config('trax-auth.services.owners', false)) {
    TraxAuth::crudRoutes(
        'trax/api/{source}/owners',
        config('trax-auth.controllers.owner', OwnerController::class)
    );
}
