<?php

use Illuminate\Support\Facades\Route;
use Trax\Auth\TraxAuth;

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/statements',
    \Trax\XapiStore\Stores\Statements\StatementController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/activities',
    \Trax\XapiStore\Stores\Activities\ActivityController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/agents',
    \Trax\XapiStore\Stores\Agents\AgentController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/activity_profiles',
    \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/agent_profiles',
    \Trax\XapiStore\Stores\AgentProfiles\AgentProfileController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/states',
    \Trax\XapiStore\Stores\States\StateController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/attachments',
    \Trax\XapiStore\Stores\Attachments\AttachmentController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/persons',
    \Trax\XapiStore\Stores\Persons\PersonController::class
);

TraxAuth::crudRoutes(
    'trax/api/{source}/xapi/ext/verbs',
    \Trax\XapiStore\Stores\Verbs\VerbController::class
);

Route::namespace('Trax\XapiStore\Controllers')->group(function () {
    Route::delete('trax/api/{source}/xapi/ext/all', 'GlobalController@clear')
        ->middleware(TraxAuth::mixedMiddleware());
});
