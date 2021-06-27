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
use Trax\Repo\Querying\Query;

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
     * The current context.
     *
     * @var array
     */
    protected $currentContext;

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
     * Get the consumer context or one of its props.
     *
     * @param  string  $prop
     * @param  \Trax\Repo\Querying\Query  $query
     * @return mixed
     */
    public function context(string $prop = null, Query $query = null)
    {
        $context = $this->getContext();

        if (!isset($prop)) {
            return $context;
        }

        if (isset($context[$prop])) {
            return $context[$prop];
        }

        if (isset($query) && $query->hasFilter($prop)) {
            return $query->filter($prop);
        }

        return null;
    }

    /**
     * Get the consumer context.
     *
     * @return mixed
     */
    public function getContext()
    {
        // Return the cached context.
        if (isset($this->currentContext)) {
            return $this->currentContext;
        }

        // A context should always have an owner_id, be it null.
        $this->currentContext = [
            'owner_id' => null
        ];

        // Common context to consumers (both users and accesses).
        $consumer = $this->consumer();
        if (!is_null($consumer)) {
            $this->currentContext = [
                'entity_id' => $consumer->entity_id,
                'owner_id' => $consumer->owner_id,
            ];
        }

        // When the consumer is an access.
        if (!$this->isUser()) {
            // Be carefull because of unit tests.
            if ($access = $this->access()) {
                $this->currentContext = [
                    'access_id' => $access->id,
                    'client_id' => $access->client->id,
                    'entity_id' => $access->client->entity_id,
                    'owner_id' => $access->client->owner_id,
                ];
            }
        }
        return $this->currentContext;
    }

    /**
     * Set the consumer context.
     *
     * @param  array  $context
     * @return void
     */
    public function setContext(array $context = null): void
    {
        $this->currentContext = $context;
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
