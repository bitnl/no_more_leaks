<?php

// Master switch. Set to false, plugin won't load.
$rcmail_config['nml_enabled'] = true;

// Debug messages: silent when false
$rcmail_config['nml_debug'] = true;

// Invalidate user's session when leak found?
// When set to false and a leak was found, a user can still browse to /?_task=mail and reach their inbox.
// default: true
$rcmail_config['nml_invalidate_session_when_leaked'] = true;

// Redirect user to a different page when leak found?
// When false, the 'leak_found.html' template for this plugin will be shown.
// default: false
$rcmail_config['nml_redirect_when_leaked'] = false;
$rcmail_config['nml_redirect_destination'] = "https://www.politie.nl/onderwerpen/no-more-leaks.html";

// Supports PHP PDO database drivers
$rcmail_config['nml_db_type'] = 'pgsql';
// if nml_db_type = sqlite, ignored otherwise:
$rcmail_config['nml_db_file'] = '/path/to/sqlite3.db';
$rcmail_config['nml_db_host'] = 'localhost';
$rcmail_config['nml_db_port'] = '5432';
$rcmail_config['nml_db_user'] = 'username';
$rcmail_config['nml_db_pass'] = 's00p3rs3kr!t';
$rcmail_config['nml_db_name'] = 'dbname';
$rcmail_config['nml_db_table_prefix'] = '';

// Comment out datasets that should not be checked
$rcmail_config['nml_datasources'] = [
    // 'testdata',

    'dataset_tabelnaam1',
    'dataset_tabelnaam2',
];

# ...
