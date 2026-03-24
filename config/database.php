<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default database connection
    |--------------------------------------------------------------------------
    | Supported values: "mysql" | "sqlite"
    */
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => env('DB_DATABASE', DATABASE_PATH . 'database.sqlite'),
        ],

        'mysql' => [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST', '127.0.0.1'),
            'port'        => env('DB_PORT', '3306'),
            'database'    => env('DB_DATABASE', ''),
            'username'    => env('DB_USERNAME', 'root'),
            'password'    => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset'     => 'utf8mb4',
        ],

    ],

];
