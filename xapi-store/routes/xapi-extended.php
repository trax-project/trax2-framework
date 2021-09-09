<?php

use Illuminate\Support\Facades\Route;
use Trax\Auth\TraxRouting;

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/statements',
    \Trax\XapiStore\Stores\Statements\StatementController::class,
    [
        'except' => ['store', 'update'],
        'destroyByQuery' => true,
    ]
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/activities',
    \Trax\XapiStore\Stores\Activities\ActivityController::class
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/agents',
    \Trax\XapiStore\Stores\Agents\AgentController::class,
    [
        'destroyByQuery' => true,
    ]
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/activity_profiles',
    \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileController::class
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/agent_profiles',
    \Trax\XapiStore\Stores\AgentProfiles\AgentProfileController::class
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/states',
    \Trax\XapiStore\Stores\States\StateController::class
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/attachments',
    \Trax\XapiStore\Stores\Attachments\AttachmentController::class,
    [
        'except' => ['store', 'update'],
    ]
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/persons',
    \Trax\XapiStore\Stores\Persons\PersonController::class
);

TraxRouting::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/verbs',
    \Trax\XapiStore\Stores\Verbs\VerbController::class
);

if (config('trax-xapi-store.logging.enabled', false)) {
    TraxRouting::mixedCrudRoutes(
        'trax/api',
        'xapi/ext/logs',
        \Trax\XapiStore\Stores\Logs\LogController::class,
        ['except' => ['store', 'destroy', 'update']]
    );
}

Route::namespace('Trax\XapiStore\Controllers')->group(function () {
    TraxRouting::mixedPostRoute('trax/api', 'xapi/ext/stores/clear', 'GlobalController@clearStores');
    TraxRouting::mixedPostRoute('trax/api', 'xapi/ext/stores/{id}/clear', 'GlobalController@clearStore');
    TraxRouting::mixedDeleteRoute('trax/api', 'xapi/ext/stores/{id}', 'GlobalController@deleteStore');
});
