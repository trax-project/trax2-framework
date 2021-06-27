<?php

use Illuminate\Support\Facades\Route;
use Trax\Auth\TraxRouting;

// Statements.
Route::namespace('Trax\XapiStore\Stores\Statements')->group(function () {
    TraxRouting::appPostRoute('trax/api', 'xapi/std/statements', 'XapiStatementController@post', 'xapi', false);
    TraxRouting::appPutRoute('trax/api', 'xapi/std/statements', 'XapiStatementController@put', 'xapi', false);
    TraxRouting::appGetRoute('trax/api', 'xapi/std/statements', 'XapiStatementController@get', 'xapi', false);
});

// Activities.
Route::namespace('Trax\XapiStore\Stores\Activities')->group(function () {
    TraxRouting::appPostRoute('trax/api', 'xapi/std/activities', 'XapiActivityController@post', 'xapi', false);
    TraxRouting::appGetRoute('trax/api', 'xapi/std/activities', 'XapiActivityController@get', 'xapi', false);
});

// Agents.
Route::namespace('Trax\XapiStore\Stores\Agents')->group(function () {
    TraxRouting::appPostRoute('trax/api', 'xapi/std/agents', 'XapiAgentController@post', 'xapi', false);
    TraxRouting::appGetRoute('trax/api', 'xapi/std/agents', 'XapiAgentController@get', 'xapi', false);
});

// States.
Route::namespace('Trax\XapiStore\Stores\States')->group(function () {
    TraxRouting::appPostRoute('trax/api', 'xapi/std/activities/state', 'XapiStateController@post', 'xapi', false);
    TraxRouting::appPutRoute('trax/api', 'xapi/std/activities/state', 'XapiStateController@put', 'xapi', false);
    TraxRouting::appGetRoute('trax/api', 'xapi/std/activities/state', 'XapiStateController@get', 'xapi', false);
    TraxRouting::appDeleteRoute('trax/api', 'xapi/std/activities/state', 'XapiStateController@delete', 'xapi', false);
});

// Activity Profiles.
Route::namespace('Trax\XapiStore\Stores\ActivityProfiles')->group(function () {
    TraxRouting::appPostRoute('trax/api', 'xapi/std/activities/profile', 'XapiActivityProfileController@post', 'xapi', false);
    TraxRouting::appPutRoute('trax/api', 'xapi/std/activities/profile', 'XapiActivityProfileController@put', 'xapi', false);
    TraxRouting::appGetRoute('trax/api', 'xapi/std/activities/profile', 'XapiActivityProfileController@get', 'xapi', false);
    TraxRouting::appDeleteRoute('trax/api', 'xapi/std/activities/profile', 'XapiActivityProfileController@delete', 'xapi', false);
});

// Agent Profiles.
Route::namespace('Trax\XapiStore\Stores\AgentProfiles')->group(function () {
    TraxRouting::appPostRoute('trax/api', 'xapi/std/agents/profile', 'XapiAgentProfileController@post', 'xapi', false);
    TraxRouting::appPutRoute('trax/api', 'xapi/std/agents/profile', 'XapiAgentProfileController@put', 'xapi', false);
    TraxRouting::appGetRoute('trax/api', 'xapi/std/agents/profile', 'XapiAgentProfileController@get', 'xapi', false);
    TraxRouting::appDeleteRoute('trax/api', 'xapi/std/agents/profile', 'XapiAgentProfileController@delete', 'xapi', false);
});

// About.
Route::namespace('Trax\XapiStore\Stores\About')->middleware('known.access')->group(function () {
    Route::get('trax/api/{source}/xapi/std/about', 'XapiAboutController@get');
});
