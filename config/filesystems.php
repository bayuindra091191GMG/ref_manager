<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'payments' => [
            'driver' => 'local',
            'root' => public_path('storage/payments'),
            'url' => env('APP_URL').'/storage/payments',
            'visibility' => 'public',
        ],

        'banners' => [
            'driver' => 'local',
            'root' => public_path('images/banners'),
            'url' => env('STORAGE_URL').'/images/banners',
            'visibility' => 'public',
        ],

        'coupons' => [
            'driver' => 'local',
            'root' => public_path('images/coupons'),
            'url' => env('STORAGE_URL').'/images/coupons',
            'visibility' => 'public',
        ],

        'employee_photos' => [
            'driver' => 'local',
            'root' => public_path('images/employee_photos'),
            'url' => env('STORAGE_URL').'/images/employee_photos',
            'visibility' => 'public',
        ],

        'package_photos' => [
            'driver' => 'local',
            'root' => public_path('storage/images/items'),
            'url' => env('STORAGE_URL').'storage/images/items',
            'visibility' => 'public',
        ],

        'items' => [
            'driver' => 'local',
            'root' => public_path('storage/images/items'),
            'url' => env('APP_URL').'/storage/images/items',
            'visibility' => 'public',
        ],

        'proofs' => [
            'driver' => 'local',
            'root' => public_path('storage/proofs'),
            'url' => env('APP_URL').'/storage/proofs',
            'visibility' => 'public',
        ],

        'receipt_pdf' => [
            'driver' => 'local',
            'root' => storage_path('app/public/receipts'),
            'url' => env('APP_URL').'/storage/receipts',
            'visibility' => 'public',
        ],

        'vouchers' => [
            'driver' => 'local',
            'root' => public_path('storage/images/vouchers'),
            'url' => env('APP_URL').'/storage/images/vouchers',
            'visibility' => 'public',
        ],

        'partners' => [
            'driver' => 'local',
            'root' => public_path('storage/images/partners'),
            'url' => env('APP_URL').'storage/images/partners',
            'visibility' => 'public',
        ],

        'members' => [
            'driver' => 'local',
            'root' => public_path('storage/members'),
            'url' => env('APP_URL').'/storage/members',
            'visibility' => 'public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
