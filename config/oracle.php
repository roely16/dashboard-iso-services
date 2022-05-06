<?php

return [
    'portales' => [
        'driver'        => 'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', '172.23.50.95'),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', 'CATGIS'),
        'username'      => env('DB_USERNAME', 'portales'),
        'password'      => env('DB_PASSWORD', 'portales2014'),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
    ],
    'rrhh' => [
        'driver'        => 'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', '172.23.50.95'),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', 'CATGIS'),
        'username'      => env('DB_USERNAME', 'rrhh'),
        'password'      => env('DB_PASSWORD', 'rrhhadmin'),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
    ],
];