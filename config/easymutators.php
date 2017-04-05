<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Filesystem Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystems on which to store added media. Choose one of the
    | filesystems you configured in app/config/filesystems.php
    |
    */

    'storage_disk' => env('EASYMUTATORS_STORAGE_DISK', 'easymutators'),

    /*
    |--------------------------------------------------------------------------
    | Default image path generator
    |--------------------------------------------------------------------------
    |
    | Default path generator class, used to generate the of files based on
    | mapping.
    |
    */

    'default_path_generator' => \Webeleven\EasyMutators\Upload\DefaultPathGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | This package uses Intervention Image internally that supports "GD Library"
    | and "Imagick" to process images. You may choose one of them according to
    | your PHP configuration. By default PHP's "GD Library" implementation is
    | used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'image_driver' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | Delete old media
    |--------------------------------------------------------------------------
    |
    | Determine if old media should be deleted from filesystem.
    |
    | Options: false, "on-set", "on-save"
    |
    */

    'delete_old_media' => 'on-save',

    /*
   |--------------------------------------------------------------------------
   | Default objects
   |--------------------------------------------------------------------------
   |
   | Default value objects classes, this is an alias array used to map to value
   | object class.
   |
   */

    'objects' => [
        'file' => \Webeleven\EasyMutators\ValueObjects\File::class,
        'image' => \Webeleven\EasyMutators\ValueObjects\Image::class,
        'settings' => \Webeleven\EasyMutators\ValueObjects\Settings::class
    ],

];
