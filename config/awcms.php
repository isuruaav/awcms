<?php

return [
    'name' => env('AWCMS_NAME', 'AWCMS'),

    'full_name' => env(
        'AWCMS_FULL_NAME',
        'Army Website Content Management System'
    ),

    'version' => env('AWCMS_VERSION', '1.0.0-dev'),

    'support_email' => env(
        'AWCMS_SUPPORT_EMAIL',
        'support@example.com'
    ),

    /*
    |--------------------------------------------------------------------------
    | Active Public Theme
    |--------------------------------------------------------------------------
    |
    | Keep this null to use the default AWCMS public views. Installed themes
    | are selected by their safe manifest slug, for example school-of-signals.
    |
    */
    'active_theme' => env('AWCMS_ACTIVE_THEME'),
];
