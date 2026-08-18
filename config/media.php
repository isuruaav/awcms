<?php

return [
    /*
     * Actual disk selection will be used by
     * Step 08B Upload Service.
     */
    'disk' => env(
        'MEDIA_DISK',
        'public',
    ),

    'directory' => 'media',

    /*
     * Upload allowlists.
     *
     * SVG and executable/web files are intentionally
     * not accepted by the first Media Library version.
     */
    'uploads' => [
        'image' => [
            'enabled' => true,

            'max_kb' => 10240,

            'mime_types' => [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
        ],

        'document' => [
            'enabled' => true,

            'max_kb' => 20480,

            'mime_types' => [
                'application/pdf',
            ],
        ],

        /*
         * Initial video support will use trusted
         * external links rather than direct uploads.
         */
        'video' => [
            'enabled' => false,

            'max_kb' => 0,

            'mime_types' => [],
        ],

        'other' => [
            'enabled' => false,

            'max_kb' => 0,

            'mime_types' => [],
        ],
    ],

    'forbidden_extensions' => [
        'php',
        'php3',
        'php4',
        'php5',
        'php7',
        'php8',
        'phtml',
        'phar',

        'cgi',
        'pl',
        'py',
        'sh',

        'exe',
        'dll',
        'bat',
        'cmd',
        'com',

        'js',
        'mjs',

        'html',
        'htm',
        'shtml',

        'svg',
        'svgz',
    ],
];
