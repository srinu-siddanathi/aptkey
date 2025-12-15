<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk Filament will use to store files. You may use any
    | of the disks defined in the `config/filesystems.php` file.
    |
    */

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where Filament's assets will be published to. It
    | is relative to the `public` directory of your Laravel application.
    |
    */

    'assets_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where Filament's cache will be stored. It is
    | relative to the `storage` directory of your Laravel application.
    |
    */

    'cache_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Livewire Loading State Path
    |--------------------------------------------------------------------------
    |
    | This is the path that Filament uses to indicate that a Livewire
    | component is loading. By default, it's `filament.core.loading`.
    |
    */

    'livewire_loading_state_path' => 'filament.core.loading',

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | By uncommenting the Laravel Echo configuration, you may connect Filament
    | to any supported broadcasting driver.
    |
    */

    'broadcasting' => [
        // 'echo' => [
        //     'broadcaster' => 'pusher',
        //     'key' => env('VITE_PUSHER_APP_KEY'),
        //     'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
        //     'forceTLS' => true,
        // ],
    ],

];

