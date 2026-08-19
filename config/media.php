<?php

return [
    /*
     * Public files may be served directly by the website.
     *
     * Internal and Restricted files must never be stored
     * on the public filesystem disk.
     */
    'disks' => [
        'public' => env(
            'MEDIA_PUBLIC_DISK',
            'public',
        ),

        'private' => env(
            'MEDIA_PRIVATE_DISK',
            'local',
        ),
    ],

    'directory' => 'media',

    'image_processing' => [
        /*
     * Prevent small compressed files with extremely
     * large pixel dimensions from exhausting GD memory.
     */
        'max_width' => 8000,

        'max_height' => 8000,

        /*
     * 6000 x 4000 = 24 megapixels.
     */
        'max_pixels' => 24000000,
    ],
    /*
     * Upload allowlists.
     *
     * MIME detection is performed using the actual
     * temporary file contents, not only browser headers.
     */
    'uploads' => [
        'image' => [
            'enabled' => true,

            'max_kb' => 10240,

            'mime_extensions' => [
                'image/jpeg' => [
                    'jpg',
                    'jpeg',
                ],

                'image/png' => [
                    'png',
                ],

                'image/webp' => [
                    'webp',
                ],
            ],
        ],

        'document' => [
            'enabled' => true,

            'max_kb' => 20480,

            'mime_extensions' => [
                'application/pdf' => [
                    'pdf',
                ],
            ],
        ],

        /*
         * Initial video support uses external links.
         */
        'video' => [
            'enabled' => false,

            'max_kb' => 0,

            'mime_extensions' => [],
        ],

        'other' => [
            'enabled' => false,

            'max_kb' => 0,

            'mime_extensions' => [],
        ],
    ],

    /*
     * Defense-in-depth denylist.
     *
     * Even if upload configuration is accidentally
     * changed later, these executable / active web
     * extensions remain forbidden.
     */
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
