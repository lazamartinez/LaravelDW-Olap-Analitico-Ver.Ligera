<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'pusher'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'useTLS' => true,
                'encrypted' => true,
                'host' => env('PUSHER_HOST', '127.0.0.1'),
                'port' => env('PUSHER_PORT', 6001),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'curl_options' => [
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ],
                // Configuración específica para sucursales
                'channel_authenticator' => \App\Services\SucursalChannelAuthenticator::class,
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            // Configuración especial para clusters de sucursales
            'options' => [
                'prefix' => 'sucursal_events:',
                'read_write_timeout' => 0,
            ],
        ],

        'log' => [
            'driver' => 'log',
            // Registrar eventos por sucursal
            'tap' => [\App\Logging\SucursalBroadcastLogger::class],
        ],

        'null' => [
            'driver' => 'null',
        ],

        // Configuración adicional para entorno local con Laravel Echo Server
        'laravel-echo-server' => [
            'driver' => 'pusher',
            'key' => env('ECHO_SERVER_KEY'),
            'secret' => env('ECHO_SERVER_SECRET'),
            'app_id' => env('ECHO_SERVER_APP_ID'),
            'options' => [
                'host' => env('ECHO_SERVER_HOST', 'localhost'),
                'port' => env('ECHO_SERVER_PORT', 6001),
                'scheme' => env('ECHO_SERVER_SCHEME', 'http'),
                'encrypted' => false,
                'verify_peer' => false,
                // Namespace para canales de sucursales
                'channel_prefix' => 'sucursal_',
            ],
        ],

        // Configuración para desarrollo con Docker
        'docker' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'host' => 'websockets',
                'port' => 6001,
                'scheme' => 'http',
                'useTLS' => false,
                'encrypted' => false,
                // Configuración específica para contenedores
                'docker_network' => 'sucursales-network',
                'allow_origin' => [
                    'http://localhost',
                    'http://host.docker.internal',
                    'http://sucursal-*.docker'
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcast Applications
    |--------------------------------------------------------------------------
    |
    | Configuración específica para aplicaciones de sucursales
    |
    */

    'applications' => [
        'sucursal' => [
            'app_id' => env('SUCURSAL_APP_ID'),
            'key' => env('SUCURSAL_APP_KEY'),
            'secret' => env('SUCURSAL_APP_SECRET'),
            'options' => [
                'cluster' => 'sucursal_cluster',
                'encrypted' => true,
            ],
            'webhooks' => [
                'channel_occupied' => env('SUCURSAL_WEBHOOK_CHANNEL_OCCUPIED'),
                'channel_vacated' => env('SUCURSAL_WEBHOOK_CHANNEL_VACATED'),
                'member_added' => env('SUCURSAL_WEBHOOK_MEMBER_ADDED'),
                'member_removed' => env('SUCURSAL_WEBHOOK_MEMBER_REMOVED'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sucursal-specific Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración adicional para el sistema de múltiples sucursales
    |
    */

    'sucursal' => [
        // Prefijo para canales de cada sucursal
        'channel_prefix' => 'sucursal_',

        // Tiempo de vida de los canales (en segundos)
        'channel_ttl' => 3600,

        // Máximo de conexiones por sucursal
        'max_connections' => 100,

        // Configuración de autenticación
        'auth' => [
            'endpoint' => '/broadcasting/auth',
            'guard' => 'sucursal_api',
        ],

        // Configuración de CORS para WebSockets
        'cors' => [
            'allowed_origins' => [
                'http://localhost',
                'http://host.docker.internal',
                'http://sucursal-*.docker'
            ],
            'allowed_methods' => ['GET', 'POST'],
            'allowed_headers' => ['Origin', 'Content-Type', 'X-Auth-Token', 'X-Sucursal-ID'],
        ],
    ],
    
    'connections' => [
        'sucursal' => [
            'driver' => 'pusher',
            'key' => env('SUCURSAL_PUSHER_KEY'),
            'secret' => env('SUCURSAL_PUSHER_SECRET'),
            'app_id' => env('SUCURSAL_PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('SUCURSAL_PUSHER_CLUSTER'),
                'useTLS' => true,
                'encrypted' => true,
                'host' => env('SUCURSAL_PUSHER_HOST', '127.0.0.1'),
                'port' => env('SUCURSAL_PUSHER_PORT', 6001),
                'scheme' => env('SUCURSAL_PUSHER_SCHEME', 'https'),
                'channel_authenticator' => \App\Services\SucursalChannelAuthenticator::class,
            ],
        ],
    ],

];
