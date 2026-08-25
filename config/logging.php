<?php

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
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
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
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
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
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
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
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
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
        ],

        'monitor_room' => [
            'driver' => 'daily',
            'path' => storage_path('logs/monitor_room/monitor_room.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'monitor_job' => [
            'driver' => 'daily',
            'path' => storage_path('logs/monitor_job/monitor_job.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'booking' => [
            'driver' => 'daily',
            'path' => storage_path('logs/booking/booking.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'package' => [
            'driver' => 'daily',
            'path' => storage_path('logs/package/package.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'partner' => [
            'driver' => 'daily',
            'path' => storage_path('logs/partner/partner.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'voucher' => [
            'driver' => 'daily',
            'path' => storage_path('logs/voucher/voucher.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'menu_access' => [
            'driver' => 'daily',
            'path' => storage_path('logs/menu_access/menu_access.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'banner' => [
            'driver' => 'daily',
            'path' => storage_path('logs/banner/banner.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'prewedding' => [
            'driver' => 'daily',
            'path' => storage_path('logs/prewedding/prewedding.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'voucher_package' => [
            'driver' => 'daily',
            'path' => storage_path('logs/voucher_package/voucher_package.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'prewedding_package' => [
            'driver' => 'daily',
            'path' => storage_path('logs/prewedding_package/prewedding_package.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'pos' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pos/pos.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'voucher_pos' => [
            'driver' => 'daily',
            'path' => storage_path('logs/voucher_pos/voucher_pos.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'otp' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/otp/otp.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'api_exception' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/exception/api_exception.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'doku_webhook' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/doku/doku_webhook.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'firebase' => [
            'driver' => 'daily',
            'path' => storage_path('logs/firebase/firebase.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],

        'master_data' => [
            'driver' => 'daily',
            'path' => storage_path('logs/master_data/master_data.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 120,
        ],
    ],

];
