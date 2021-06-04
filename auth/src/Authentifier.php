<?php

namespace Trax\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Trax\Auth\Middleware\ApiMiddleware;
use Trax\Auth\Stores\Accesses\AccessService;
use Trax\Auth\Authorizer;
use Trax\Auth\Stores\Accesses\Access;

class Authentifier
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The access repository.
     *
     * @var \Trax\Auth\Stores\Accesses\AccessService
     */
    protected $accesses;

    /**
     * The current access.
     *
     * @var \Trax\Auth\Stores\Accesses\Access
     */
    protected $currentAccess;

    /**
     * Create a the auth services.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Trax\Auth\Stores\Accesses\AccessService  $accesses
     * @return void
     */
    public function __construct(Application $app, AccessService $accesses)
    {
        $this->app = $app;
        $this->accesses = $accesses;
    }

    /**
     * Get the authorizer services.
     *
     * @return \Trax\Auth\Authorizer
     */
    public function authorizer(): Authorizer
    {
        return $this->app->make(Authorizer::class);
    }

    /**
     * Register the authentication API routes.
     *
     * @return void
     */
    public function authApiRoutes(): void
    {
        Route::namespace('Trax\Auth\Stores\Users')->middleware('web')->group(function () {

            // Main routes.
            Route::post('trax/api/auth/login', 'Auth\LoginController@login')->name('login.post');
            Route::post('trax/api/auth/logout', 'Auth\LoginController@logout')->name('logout');

            // Registration routes.
            if (config('trax-auth.user.register', false)) {
                Route::post('trax/api/auth/register', 'Auth\RegisterController@register')->name('register.post');
            }

            // Password reset routes.
            if (config('trax-auth.user.reset', false)) {
                Route::post('trax/api/auth/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')
                    ->name('password.email');
                Route::post('trax/api/auth/password/reset', 'Auth\ResetPasswordController@reset')
                    ->name('password.update');

                // This is the route of the reset view.
                // We need it because the reset notification includes a link to this view.
                Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetFormOrRedirect')
                    ->name('password.reset');
            }

            // Password confirm routes.
            if (config('trax-auth.user.confirm', false)) {
                Route::post('trax/api/auth/password/confirm', 'Auth\ConfirmPasswordController@confirm')
                    ->name('password.confirm.post');
            }

            // Password verification routes.
            if (config('trax-auth.user.verify', false)) {
                Route::get('trax/api/auth/email/verify/{id}/{hash}', 'Auth\VerificationController@verify')
                    ->name('verification.verify');
                Route::post('trax/api/auth/email/resend', 'Auth\VerificationController@resend')
                    ->name('verification.resend');
            }
        });
    }

    /**
     * Register the authentication Web routes (views).
     *
     * @param  string $prefix
     * @return void
     */
    public function authWebRoutes(string $prefix = 'front'): void
    {
        $prefix = "$prefix/auth/";
        Route::namespace('Trax\Auth\Stores\Users')->middleware('web')->group(function () use ($prefix) {

            // Main routes.
            Route::get($prefix.'login', 'Auth\LoginController@showLoginForm')->name('login');

            // Registration routes.
            if (config('trax-auth.user.register', false)) {
                Route::get($prefix.'register', 'Auth\RegisterController@showRegistrationForm')->name('register');
            }

            // Password reset routes.
            if (config('trax-auth.user.reset', false)) {
                Route::get($prefix.'password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')
                    ->name('password.request');
                Route::get($prefix.'password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')
                    ->name('password.reset');
            }

            // Password confirm routes.
            if (config('trax-auth.user.confirm', false)) {
                Route::get($prefix.'password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')
                    ->name('password.confirm');
            }

            // Password verification routes.
            if (config('trax-auth.user.verify', false)) {
                Route::get($prefix.'email/verify', 'Auth\VerificationController@show')->name('verification.notice');
            }
        });
    }

    /**
     * Define a GET route.
     *
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  string  $target
     * @param  string|array  $middlewares
     * @param  bool  $secure
     * @return void
     */
    public function mixedGetRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $this->userGetRoute($prefix, $suffix, $target, $middlewares, $secure);
        $this->appGetRoute($prefix, $suffix, $target, $middlewares, $secure);
    }

    public function userGetRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->userMiddleware($middlewares, $secure);
        Route::get($prefix . '/front/' . $suffix, $target)->middleware($middleware);
    }
    
    public function appGetRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->appMiddleware($middlewares, $secure);
        Route::get($prefix . '/{source}/' . $suffix, $target)->middleware($middleware);
    }

    /**
     * Define a POST route.
     *
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  string  $target
     * @param  string|array  $middlewares
     * @param  bool  $secure
     * @return void
     */
    public function mixedPostRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $this->userPostRoute($prefix, $suffix, $target, $middlewares, $secure);
        $this->appPostRoute($prefix, $suffix, $target, $middlewares, $secure);
    }

    public function userPostRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->userMiddleware($middlewares, $secure);
        Route::post($prefix . '/front/' . $suffix, $target)->middleware($middleware);
    }
    
    public function appPostRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->appMiddleware($middlewares, $secure);
        Route::post($prefix . '/{source}/' . $suffix, $target)->middleware($middleware);
    }

    /**
     * Define a PUT route.
     *
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  string  $target
     * @param  string|array  $middlewares
     * @param  bool  $secure
     * @return void
     */
    public function mixedPutRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $this->userPutRoute($prefix, $suffix, $target, $middlewares, $secure);
        $this->appPutRoute($prefix, $suffix, $target, $middlewares, $secure);
    }

    public function userPutRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->userMiddleware($middlewares, $secure);
        Route::put($prefix . '/front/' . $suffix, $target)->middleware($middleware);
    }
    
    public function appPutRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->appMiddleware($middlewares, $secure);
        Route::put($prefix . '/{source}/' . $suffix, $target)->middleware($middleware);
    }

    /**
     * Define a DELETE route.
     *
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  string  $target
     * @param  string|array  $middlewares
     * @param  bool  $secure
     * @return void
     */
    public function mixedDeleteRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $this->userDeleteRoute($prefix, $suffix, $target, $middlewares, $secure);
        $this->appDeleteRoute($prefix, $suffix, $target, $middlewares, $secure);
    }

    public function userDeleteRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->userMiddleware($middlewares, $secure);
        Route::delete($prefix . '/front/' . $suffix, $target)->middleware($middleware);
    }
    
    public function appDeleteRoute(string $prefix, string $suffix, string $target, $middlewares = [], $secure = true): void
    {
        $middleware = $this->appMiddleware($middlewares, $secure);
        Route::delete($prefix . '/{source}/' . $suffix, $target)->middleware($middleware);
    }

    /**
     * Register all the CRUD routes of a repository.
     *
     * @param  string  $prefix
     * @param  string  $suffix
     * @param  string  $controllerClass
     * @param  array  $options
     * @return void
     */
    public function mixedCrudRoutes(string $prefix, string $suffix, string $controllerClass, array $options = []): void
    {
        $this->userCrudRoutes($prefix, $suffix, $controllerClass, $options);
        $this->appCrudRoutes($prefix, $suffix, $controllerClass, $options);
    }

    public function userCrudRoutes(string $prefix, string $suffix, string $controllerClass, array $options = []): void
    {
        $endpoint = $prefix . '/front/' . $suffix;
        $this->crudRoutes($endpoint, $controllerClass, $this->userMiddleware(), $options);
    }

    public function appCrudRoutes(string $prefix, string $suffix, string $controllerClass, array $options = []): void
    {
        $endpoint = $prefix . '/{source}/' . $suffix;
        $this->crudRoutes($endpoint, $controllerClass, $this->appMiddleware(), $options);
    }

    /**
     * Register all the CRUD routes of a repository with a given endpoint.
     *
     * @param  string  $endpoint
     * @param  string  $controllerClass
     * @param  array  $middlewares
     * @param  array  $options
     * @return void
     */
    public function crudRoutes(string $endpoint, string $controllerClass, array $middlewares, array $options = []): void
    {
        Route::middleware($middlewares)->group(function () use ($endpoint, $controllerClass, $options) {
            
            // Standard CRUD routes.
            $apiOptions = [
                // We remove all the route names to avoid some conflicts.
                'names' => ['index' => '', 'store' =>'',  'destroy' =>'',  'update' =>'',  'show' =>'']
            ];
            if (isset($options['except'])) {
                $apiOptions['except'] = $options['except'];
            }
            Route::apiResource($endpoint, $controllerClass, $apiOptions);
            $namespace = implode("\\", array_slice(explode("\\", $controllerClass), 0, -1));

            // Determine the name of the resource param.
            $paramName = \Str::of($endpoint)->afterLast('/')->singular()->replace('-', '_');

            // Additional routes.
            Route::namespace($namespace)->group(function () use ($endpoint, $controllerClass, $options, $paramName) {

                // Count route.
                Route::get($endpoint . "/count", class_basename($controllerClass) . '@count');
    
                // Duplicate route.
                if (!empty($options['duplicate'])) {
                    Route::post($endpoint . "/{{$paramName}}/duplicate", class_basename($controllerClass) . '@duplicate');
                }

                // Delete by query route.
                if (!empty($options['destroyByQuery'])) {
                    Route::delete($endpoint, class_basename($controllerClass) . '@destroyByQuery');
                }
            });
        });
    }

    /**
     * Return the middlewares for the web UI.
     *
     * @param  string|array  $middlewares
     * @param  bool  $secure
     * @return array
     */
    public function userMiddleware($middlewares = [], $secure = true): array
    {
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }
        $middlewares = array_merge(['web', 'auth'], $middlewares);
        if (config('trax-auth.user.verify', false) && !$this->app->runningUnitTests()) {
            $middlewares[] = 'verified';
        }
        return $middlewares;
    }

    /**
     * Return the middlewares for pure external API protection.
     *
     * @param  string|array  $middlewares
     * @param  bool  $secure
     * @return array
     */
    public function appMiddleware($middlewares = [], $secure = true): array
    {
        // Additional middleware added as a string.
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }
        // Ultra security option.
        // This may be deactivated for some reasons.
        if ($secure && !$this->app->runningUnitTests()) {
            $middlewares[] = 'throttle:60,1';
        }
        return array_merge([ApiMiddleware::class], $middlewares);
    }

    /**
     * Is the consumer a user (or an app)?.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        // For unit tests, we use an alternative way to check the consumer origin because Request are not still ready.
        // Add putenv("TRAX_AUTH_IS_USER=true"); before parent::setUp(); in the unit test to force user auth.
        if ($this->app->runningUnitTests()) {
            return getenv("TRAX_AUTH_IS_USER") === 'true';
        }

        // Console commands.
        if ($this->app->runningInConsole()) {
            return false;
        }

        // The route segments are not available, so for now, we consider that third-party applications
        // always use an authorization header, and the front does not..
        if ($this->app['request']->headers->has('authorization')) {
            return false;
        }

        // We also consider alternative POST requests with a 'method' param in the query string.
        // This kind of request always comes from a third-party application.
        $method = $this->app['request']->method();
        $query = $this->app['request']->query();
        if ($method == 'POST' && isset($query['method'])) {
            return false;
        }

        // In all other cases, the consumer is a user.
        return true;
    }

    /**
     * Check if the consumer is the console.
     *
     * @return bool
     */
    public function console()
    {
        return $this->app->runningInConsole();
    }

    /**
     * Get the consumer: app client or authenticated user.
     * May be null when running in console.
     *
     * @return \Trax\Auth\Stores\Accesses\Access|\Trax\Auth\Stores\Users\User|null
     */
    public function consumer()
    {
        return $this->isUser() ? Auth::user() : $this->access();
    }

    /**
     * Get the authenticated user.
     *
     * @return \Trax\Auth\Stores\Users\User
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function user()
    {
        if (!$this->isUser()) {
            throw new AuthorizationException("Forbidden: apps can't get the authenticated user.");
        }
        return Auth::user();
    }

    /**
     * Get the authentication access.
     *
     * @return \Trax\Auth\Stores\Accesses\Access|null
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function access()
    {
        if ($this->isUser()) {
            throw new AuthorizationException("Forbidden: users can't get the connected app.");
        }
        return $this->currentAccess;
    }

    /**
     * Get the consumer context.
     *
     * @return array
     */
    public function context(): array
    {
        // A context should always have an owner_id, be it null.
        $context = [
            'owner_id' => null
        ];

        // Common context to consumers (both users and accesses).
        $consumer = $this->consumer();
        if (!is_null($consumer)) {
            $context = [
                'entity_id' => $consumer->entity_id,
                'owner_id' => $consumer->owner_id,
            ];
        }

        // When the consumer is an access.
        $access = $this->access();
        if (!is_null($access)) {
            $context = [
                'access_id' => $access->id,
                'client_id' => $access->client->id,
                'entity_id' => $access->client->entity_id,
                'owner_id' => $access->client->owner_id,
            ];
        }
        return $context;
    }

    /**
     * Get an access guard given its type.
     *
     * @param  string  $type
     * @return \Trax\Auth\Contracts\AccessGuardContract
     */
    public function guard(string $type)
    {
        $guard = config('trax-auth.app.guards', [
            'basic_http' => \Trax\Auth\Stores\BasicHttp\BasicHttpGuard::class,
        ])[$type];
        return new $guard;
    }

    /**
     * Get data to select a guard.
     *
     * @return array
     */
    public function guardsSelect(): array
    {
        $guards = config('trax-auth.app.guards', [
            'basic_http' => \Trax\Auth\Stores\BasicHttp\BasicHttpGuard::class,
        ]);
        return collect($guards)->map(function ($class) {
            return (new $class)->name();
        })->toArray();
    }

    /**
     * Set the current access.
     *
     * @param  \Trax\Auth\Stores\Accesses\Access  $access
     * @return void
     */
    public function setAccess(Access $access): void
    {
        $this->currentAccess = $access;
    }
}
