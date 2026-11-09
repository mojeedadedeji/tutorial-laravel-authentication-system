<?php

return [

    /*
     * CORS for the DECLUTTER NG prototype front-end (static app on
     * localhost during development). Token auth is used, so
     * supports_credentials stays false.
     */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_merge(
        [
            'http://localhost:8123',
            'http://localhost:8000',
            'http://127.0.0.1:8123',
            'http://127.0.0.1:8000',
        ],
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    )),

    'allowed_origins_patterns' => [
        '#^https://([a-z0-9-]+\.)?netlify\.app$#',
        '#^https://declutter-ng\.[a-z0-9.-]+#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
