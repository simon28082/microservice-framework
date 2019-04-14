<?php

use CrCms\Server\Drivers\Laravel\Resetters;

return [
    /*
    |--------------------------------------------------------------------------
    | Swoole servers
    |--------------------------------------------------------------------------
    |
    | All swoole server collections
    | *.settings.log_level: 0 =>DEBUG 1 =>TRACE 2 =>INFO 3 =>NOTICE 4 =>WARNING 5 =>ERROR
    |
    */

    'servers' => [
        'ms' => [
            'driver' => CrCms\Microservice\Server\Http\Server::class,
            'host' => env('SERVER_HTTP_HOST', '0.0.0.0'),
            'port' => env('SERVER_HTTP_PORT', 28080),
            'settings' => [
                'user' => env('SERVER_USER'),
                'group' => env('SERVER_GROUP'),
                'log_level' => env('SERVER_LOG_LEVEL', 4),
                'log_file' => storage_path('logs/ms.log'),
            ],
        ],
    ],

    'laravel' => [

        /*
        |--------------------------------------------------------------------------
        | Laravel initialize application
        |--------------------------------------------------------------------------
        |
        | Must be realized CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract
        |
        */

        'app' => \CrCms\Microservice\Console\ServerApplication::class,

        /*
        |--------------------------------------------------------------------------
        | Laravel preload instance
        |--------------------------------------------------------------------------
        |
        | Load the parsed instance ahead of time
        | This parsing will be an instance of all request sharing for the current worker.
        |
        */

        'preload' => [
            'cache', 'events', 'cache.store', 'encrypter', 'db', 'files', 'filesystem', 'hash', 'hash.driver', 'translator', 'log', 'validator', 'queue',
        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel reload providers
        |--------------------------------------------------------------------------
        |
        | Information file for saving all running processes
        |
        */

        'providers' => [

        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel resetters
        |--------------------------------------------------------------------------
        |
        | Every time you need to load an object that needs to be reset
        | Please note the order of execution of the load
        |
        */

        'resetters' => [
            Resetters\ConfigResetter::class,
            Resetters\ProviderResetter::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel events
        |--------------------------------------------------------------------------
        |
        | Available events
        | start: onStart
        | worker_start: onWorkerStart
        | request: onRequest
        |
        */

        'events' => [
        ],
    ],
];
