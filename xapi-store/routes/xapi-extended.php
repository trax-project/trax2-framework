<?php

use Illuminate\Support\Facades\Route;
use Trax\Auth\TraxAuth;

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/statements',
    \Trax\XapiStore\Stores\Statements\StatementController::class,
    ['destroyByQuery' => true]
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/activities',
    \Trax\XapiStore\Stores\Activities\ActivityController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/agents',
    \Trax\XapiStore\Stores\Agents\AgentController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/activity_profiles',
    \Trax\XapiStore\Stores\ActivityProfiles\ActivityProfileController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/agent_profiles',
    \Trax\XapiStore\Stores\AgentProfiles\AgentProfileController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/states',
    \Trax\XapiStore\Stores\States\StateController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/attachments',
    \Trax\XapiStore\Stores\Attachments\AttachmentController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/persons',
    \Trax\XapiStore\Stores\Persons\PersonController::class
);

TraxAuth::mixedCrudRoutes(
    'trax/api',
    'xapi/ext/verbs',
    \Trax\XapiStore\Stores\Verbs\VerbController::class
);

Route::namespace('Trax\XapiStore\Controllers')->group(function () {
    TraxAuth::mixedDeleteRoute('trax/api', 'xapi/ext/stores/{id}', 'GlobalController@clearStore');
});
