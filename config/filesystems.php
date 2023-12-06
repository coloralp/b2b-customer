<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],
        'back_up' => [
            'driver' => 'local',
            'root' => public_path('back'),
            'throw' => false,
        ],
        'google' => [
            'driver' => 'google',
            'clientId' => config('backup-info.GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => config('backup-info.GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => config('backup-info.GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folderId' => config('backup-info.GOOGLE_DRIVE_BACKUP_FOLDER_ID'),
        ],

        'image' => [
            'driver' => 'local',
            'root' => storage_path('app/images'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'order_contents' => [
            'driver' => 'local',
            'root' => storage_path('app/order_contents'),
            'url' => env('APP_URL') . '/storage/order_contents',
            'visibility' => 'public',
            'throw' => false,
        ],

        'orderZip' => [
            'driver' => 'local',
            'root' => storage_path('app/orderZip'),
            'url' => env('APP_URL') . '/storage/orderZip',
            'visibility' => 'local',
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
        'filter_keys' => [
            'driver' => 'local',
            'root' => storage_path('app/public/FilterKeys'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'order_exports' => [
            'driver' => 'local',
            'root' => storage_path('app/OrderExports'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'stock_exports' => [
            'driver' => 'local',
            'root' => storage_path('app/StockExports'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'key_exports' => [
            'driver' => 'local',
            'root' => storage_path('app/KeyExports'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'kinguin_logs' => [
            'driver' => 'local',
            'root' => storage_path('logs/kinguin'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'eneba_logs' => [
            'driver' => 'local',
            'root' => storage_path('logs/eneba'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'gamivo_logs' => [
            'driver' => 'local',
            'root' => storage_path('logs/gamivo'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'rar_files' => [
            'driver' => 'local',
            'root' => storage_path('app/order_contents'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'auth_etail' => [
            'driver' => 'local',
            'root' => storage_path('logs/auth'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'update_price_cron' => [
            'driver' => 'local',
            'root' => storage_path('logs/cron'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'auth_log' => [
            'driver' => 'local',
            'root' => storage_path('logs/auth/white_list.log'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'horizon_log' => [
            'driver' => 'local',
            'root' => storage_path('logs/auth/horizon_auth.log'),
            'visibility' => 'public',
            'throw' => false,
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
