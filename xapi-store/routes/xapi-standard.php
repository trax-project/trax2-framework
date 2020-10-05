<?php

use Illuminate\Support\Facades\Route;
use Trax\Auth\TraxAuth;

// Statements.
Route::namespace('Trax\XapiStore\Stores\Statements')->middleware(TraxAuth::mixedMiddleware('xapi', false))->group(function () {
    Route::post('trax/api/{source}/xapi/std/statements', 'XapiStatementController@post');
    Route::put('trax/api/{source}/xapi/std/statements', 'XapiStatementController@put');
    Route::get('trax/api/{source}/xapi/std/statements', 'XapiStatementController@get');
});

// Activities.
Route::namespace('Trax\XapiStore\Stores\Activities')->middleware(TraxAuth::mixedMiddleware('xapi', false))->group(function () {
    Route::post('trax/api/{source}/xapi/std/activities', 'XapiActivityController@post');
    Route::get('trax/api/{source}/xapi/std/activities', 'XapiActivityController@get');
});

// Agents.
Route::namespace('Trax\XapiStore\Stores\Agents')->middleware(TraxAuth::mixedMiddleware('xapi', false))->group(function () {
    Route::post('trax/api/{source}/xapi/std/agents', 'XapiAgentController@post');
    Route::get('trax/api/{source}/xapi/std/agents', 'XapiAgentController@get');
});

// States.
Route::namespace('Trax\XapiStore\Stores\States')->middleware(TraxAuth::mixedMiddleware('xapi', false))->group(function () {
    Route::post('trax/api/{source}/xapi/std/activities/state', 'XapiStateController@post');
    Route::put('trax/api/{source}/xapi/std/activities/state', 'XapiStateController@put');
    Route::get('trax/api/{source}/xapi/std/activities/state', 'XapiStateController@get');
    Route::delete('trax/api/{source}/xapi/std/activities/state', 'XapiStateController@delete');
});

// Activity Profiles.
Route::namespace('Trax\XapiStore\Stores\ActivityProfiles')->middleware(TraxAuth::mixedMiddleware('xapi', false))->group(function () {
    Route::post('trax/api/{source}/xapi/std/activities/profile', 'XapiActivityProfileController@post');
    Route::put('trax/api/{source}/xapi/std/activities/profile', 'XapiActivityProfileController@put');
    Route::get('trax/api/{source}/xapi/std/activities/profile', 'XapiActivityProfileController@get');
    Route::delete('trax/api/{source}/xapi/std/activities/profile', 'XapiActivityProfileController@delete');
});

// Agent Profiles.
Route::namespace('Trax\XapiStore\Stores\AgentProfiles')->middleware(TraxAuth::mixedMiddleware('xapi', false))->group(function () {
    Route::post('trax/api/{source}/xapi/std/agents/profile', 'XapiAgentProfileController@post');
    Route::put('trax/api/{source}/xapi/std/agents/profile', 'XapiAgentProfileController@put');
    Route::get('trax/api/{source}/xapi/std/agents/profile', 'XapiAgentProfileController@get');
    Route::delete('trax/api/{source}/xapi/std/agents/profile', 'XapiAgentProfileController@delete');
});

// About.
Route::namespace('Trax\XapiStore\Stores\About')->middleware('known.access')->group(function () {
    Route::get('trax/api/{source}/xapi/std/about', 'XapiAboutController@get');
});
