<?php

return [

    // Authorization scopes.
    'xapi_scopes_name' => 'xAPI authorization scopes',
    'xapi_scopes_description' => 'Authorization scopes defined by the xAPI spec.',

    // Extra permissions.
    'xapi_extra_name' => 'xAPI extra-permissions',
    'xapi_extra_description' => 'Additional permissions to manage xAPI data.',


    // Authorization scopes.

    'all_name' => 'All',
    'all_description' => 'Unrestricted read and write access.',

    'all_read_name' => 'All/Read',
    'all_read_description' => 'Unrestricted read access.',

    'statements_write_name' => 'Statements/Write',
    'statements_write_description' => 'Write any statement.',

    'statements_read_mine_client_name' => 'Statements/Read/Mine (client)',
    'statements_read_mine_client_description' => 'Read statements written by the same client.',
        
    'statements_read_mine_access_name' => 'Statements/Read/Mine (access)',
    'statements_read_mine_access_description' => 'Read statements written by the same access.',
    
    'statements_read_name' => 'Statements/Read',
    'statements_read_description' => 'Read any statement.',

    'define_name' => 'Define',
    'define_description' => '(re)Define activities.
If storing a statement when this is not granted, ids will be saved
but activity definitions will not be saved nor updated.',

    'state_mine_client_name' => 'State/Mine (client)',
    'state_mine_client_description' => 'Read/Write state data
initially written by the same client.',

    'state_mine_access_name' => 'State/Mine (access)',
    'state_mine_access_description' => 'Read/Write state data
initially written by the same access.',

    'profile_mine_client_name' => 'Profile/Mine (client)',
    'profile_mine_client_description' => 'Read/Write profile document data
initially written by the same client.',

    'profile_mine_access_name' => 'Profile/Mine (access)',
    'profile_mine_access_description' => 'Read/Write profile document data
initially written by the same access.',


    // Extra permissions.

    'observe_name' => 'Observe xAPI data',
    'observe_description' => 'Access xAPI data (read-only).',

    'manage_name' => 'Manage xAPI data',
    'manage_description' => 'Manage xAPI data, including deletion.',

    'extended_request_name' => 'Extended API',
    'extended_request_description' => 'Access xAPI data (read-only) and build complex requests.',
];
