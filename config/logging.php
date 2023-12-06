<?php

use App\Enums\MarketplaceName;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'permission' => 0666,
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'permission' => 0666,
            'days' => 14,
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'permission' => 0666,
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
            'permission' => 0666,
        ],

        MarketplaceName::KINGUIN->name . '_success' => [
            'driver' => 'daily',
            'path' => storage_path('logs/kinguin/kinguin_success.log'),
            'permission' => 0666,
        ],
        MarketplaceName::KINGUIN->name . '_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/kinguin/kinguin_error.log'),
            'permission' => 0666,
        ],

        MarketplaceName::ENEBA->name . '_success' => [
            'driver' => 'daily',
            'path' => storage_path('logs/eneba/eneba_success.log'),
            'permission' => 0666,
        ],

        MarketplaceName::ENEBA->name . '_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/eneba/eneba_error.log'),
            'permission' => 0666,
        ],

        MarketplaceName::GAMIVO->name . '_success' => [
            'driver' => 'daily',
            'path' => storage_path('logs/gamivo/gamivo_success.log'),
            'permission' => 0666,
        ],

        MarketplaceName::GAMIVO->name . '_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/gamivo/gamivo_error.log'),
            'permission' => 0666,
        ],

        MarketplaceName::K4G->name . '_success' => [
            'driver' => 'daily',
            'path' => storage_path('logs/k4g/k4g_success.log'),
            'permission' => 0666,
        ],

        MarketplaceName::K4G->name . '_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/k4g/k4g_error.log'),
            'permission' => 0666,
        ],

        'test_log' => [
            'driver' => 'daily',
            'path' => storage_path('logs/test/test.log'),
            'permission' => 0666,
        ],

        'create_order_etail' => [
            'driver' => 'daily',
            'path' => storage_path('logs/test/create_order_etail.log'),
            'permission' => 0666,
        ],

        'create_order_etail1' => [
            'driver' => 'daily',
            'path' => storage_path('logs/test/kins.log'),
            'permission' => 0666,
        ],
        'broke_keys' => [
            'driver' => 'daily',
            'path' => storage_path('logs/test/broke.log'),
            'permission' => 0666,
        ],
        'horizon_log' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth/horizon_auth.log'),
            'permission' => 0666,
        ],
        'auth_log' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth/white_list.log'),
            'permission' => 0666,
        ],

        'update_price_cron' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cron/marketplaces_price_update.log'),
            'permission' => 0666,
        ],
        'auth_etail' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth/etail_auth.log'),
            'permission' => 0666,
        ],
        'general_log' => [
            'driver' => 'daily',
            'path' => storage_path('logs/general/general.log'),
            'permission' => 0666,
        ],
        'myTest' => [
            'driver' => 'daily',
            'path' => storage_path('logs/myTest/myTest.log'),
            'permission' => 0666,
        ]


    ],

];
