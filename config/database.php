<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'mongodb'),

    'connections' => [

        'mongodb' => [
            'driver'   => 'mongodb',
            'dsn'      => env('MONGODB_URI'),
            'database' => env('MONGODB_DATABASE', 'TerraMasterDb'),
        ],

        // Other connection configurations (like SQLite, MySQL, etc.) can go here
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        // Redis configurations...
    ],
];
