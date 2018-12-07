<?php

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
        'http' => [
            'driver' => CrCms\Microservice\Server\Http\Server::class,
            'host' => '0.0.0.0',
            'port' => 28888,
            'mode' => defined('SWOOLE_PROCESS') ? SWOOLE_PROCESS : 3,
            'type' => defined('SWOOLE_SOCK_TCP') ? SWOOLE_SOCK_TCP : 1,
            'settings' => [
                'user' => env('SWOOLE_USER'),
                'group' => env('SWOOLE_GROUP'),
                'log_level' => 4,
                'log_file' => storage_path('logs/http.log'),
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Register reload provider events
    |--------------------------------------------------------------------------
    |
    | Information file for saving all running processes
    |
    */
    'reload_provider_events' => [
        \CrCms\Microservice\Server\Events\RequestHandled::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel reload providers
    |--------------------------------------------------------------------------
    |
    | Information file for saving all running processes
    |
    */

    'reload_providers' => [

    ],
];