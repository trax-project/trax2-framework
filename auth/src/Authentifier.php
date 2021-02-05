<?php

namespace Trax\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Trax\Auth\Middleware\ApiMiddleware;
use Trax\Auth\Stores\Accesses\AccessService;
use Trax\Auth\Authorizer;

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
     * Register all the CRUD routes of a repository with a mixed middleware.
     *
     * @param  string  $endpoint
     * @param  string  $controllerClass
     * @param  array  $options
     * @return void
     */
    public function crudRoutes(string $endpoint, string $controllerClass, array $options = []): void
    {
        Route::middleware($this->mixedMiddleware())->group(function () use ($endpoint, $controllerClass, $options) {
            
            // Standard CRUD routes.
            Route::apiResource($endpoint, $controllerClass);
            $namespace = implode("\\", array_slice(explode("\\", $controllerClass), 0, -1));

            // Determine the name of the resource param.
            $paramName = \Str::of($endpoint)->afterLast('/')->singular()->replace('-', '_');

            // Duplicate route.
            if (!empty($options['duplicate'])) {
                Route::namespace($namespace)->group(function () use ($endpoint, $controllerClass, $paramName) {
                    Route::post($endpoint . "/{{$paramName}}/duplicate", class_basename($controllerClass) . '@duplicate');
                });
            }

            // Delete by query route.
            if (!empty($options['destroyByQuery'])) {
                Route::namespace($namespace)->group(function () use ($endpoint, $controllerClass) {
                    Route::delete($endpoint, class_basename($controllerClass) . '@destroyByQuery');
                });
            }
        });
    }

    /**
     * Return the middlewares for the web UI.
     *
     * @param  string|array  $middlewares
     * @return array
     */
    public function userMiddleware($middlewares = []): array
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
     * Return the middlewares for mixed protection (external API & web session).
     *
     * @param  string|array|null  $middlewares
     * @param  bool  $secure
     * @return array
     */
    public function mixedMiddleware($middlewares = [], $secure = true): array
    {
        return $this->isUser() ? $this->userMiddleware($middlewares) : $this->appMiddleware($middlewares, $secure);
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
     * Check an access given its UUID.
     *
     * @param  string  $uuid
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function checkAccess(string $uuid, Request $request): void
    {
        // We find the access for this source.
        if (!$access = $this->accesses->findByUuid($uuid)) {
            throw new AuthenticationException();
        }

        // We check that the access is active.
        if (!$access->isActive()) {
            throw new AuthenticationException();
        }

        // We check access.
        $guard = $this->guard($access->type);
        if (!$guard->check($access->credentials, $request)) {
            throw new AuthenticationException();
        }

        // Record the manager.
        $this->currentAccess = $access;
    }
}
