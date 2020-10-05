<?php

namespace Trax\XapiStore;

use Illuminate\Support\ServiceProvider;
use Trax\XapiStore\Stores\Statements\StatementService;
use Trax\XapiStore\Stores\Agents\AgentService;
use Trax\XapiStore\Stores\Activities\ActivityRepository;
use Trax\XapiStore\Stores\Attachments\AttachmentRepository;
use Trax\XapiStore\Stores\Persons\PersonRepository;
use Trax\XapiStore\Stores\Verbs\VerbRepository;
use Trax\Auth\Traits\RegisterPermissionProviders;

class XapiStoreServiceProvider extends ServiceProvider
{
    use RegisterPermissionProviders;

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        'xapi' => \Trax\XapiStore\Middleware\XapiMiddleware::class,
        \Illuminate\Contracts\Debug\ExceptionHandler::class => \Trax\XapiStore\Exceptions\XapiExceptionHandler::class,
        \Trax\XapiStore\Stores\Statements\StatementRepository::class => \Trax\XapiStore\Stores\Statements\StatementRepository::class,
        \Trax\XapiStore\Stores\Activities\ActivityRepository::class => \Trax\XapiStore\Stores\Activities\ActivityRepository::class,
        \Trax\XapiStore\Stores\Agents\AgentRepository::class => \Trax\XapiStore\Stores\Agents\AgentRepository::class,
        \Trax\XapiStore\Stores\States\StateRepository::class => \Trax\XapiStore\Stores\States\StateRepository::class,
        \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository::class => \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileRepository::class,
        \Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository::class => \Trax\XapiStore\Stores\AgentProfiles\AgentProfileRepository::class,
        \Trax\XapiStore\Stores\Attachments\AttachmentRepository::class => \Trax\XapiStore\Stores\Attachments\AttachmentRepository::class,
        \Trax\XapiStore\Stores\Persons\PersonRepository::class => \Trax\XapiStore\Stores\Persons\PersonRepository::class,
        \Trax\XapiStore\Stores\Verbs\VerbRepository::class => \Trax\XapiStore\Stores\Verbs\VerbRepository::class,
    ];

    /**
     * List of permission providers that should be registered.
     *
     * @var array
     */
    protected $permissionProviders = [
        'xapi-scopes' => \Trax\XapiStore\Stores\All\ScopesPermissions::class,
        'xapi-extra' => \Trax\XapiStore\Stores\All\ExtraPermissions::class,
        'statement' => \Trax\XapiStore\Stores\Statements\StatementPermissions::class,
        'activity' => \Trax\XapiStore\Stores\Activities\ActivityPermissions::class,
        'agent' => \Trax\XapiStore\Stores\Agents\AgentPermissions::class,
        'state' => \Trax\XapiStore\Stores\States\StatePermissions::class,
        'activity_profile' => \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfilePermissions::class,
        'agent_profile' => \Trax\XapiStore\Stores\AgentProfiles\AgentProfilePermissions::class,
        'attachment' => \Trax\XapiStore\Stores\Attachments\AttachmentPermissions::class,
        'person' => \Trax\XapiStore\Stores\Persons\PersonPermissions::class,
        'verb' => \Trax\XapiStore\Stores\Verbs\VerbPermissions::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Agent service.
        $this->app->singleton(AgentService::class, function () {
            $persons = $this->app->make(PersonRepository::class);
            return new AgentService($persons);
        });

        // Statement service.
        $this->app->singleton(StatementService::class, function () {
            $activities = $this->app->make(ActivityRepository::class);
            $attachments = $this->app->make(AttachmentRepository::class);
            $agents = $this->app->make(AgentService::class);
            $persons = $this->app->make(PersonRepository::class);
            $verbs = $this->app->make(VerbRepository::class);
            return new StatementService($activities, $attachments, $agents, $persons, $verbs);
        });
    }

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
            $this->loadMigrationsFrom(__DIR__.'/../../' . 'xapi-store/database/migrations');
        }

        // Load translations.
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'trax-xapi-store');

        // Define routes.
        $this->loadRoutesFrom(__DIR__.'/../routes/xapi-standard.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/xapi-extended.php');

        // Define permissions.
        $this->registerPermissionProviders();
    }
}
