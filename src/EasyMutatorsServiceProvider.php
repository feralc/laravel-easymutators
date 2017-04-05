<?php

namespace Webeleven\EasyMutators;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use Webeleven\EasyMutators\Upload\TempFileUploader;

class EasyMutatorsServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/easymutators.php' => config_path('easymutators.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton(MediaService::class, function() {

            $storage = $this->getStorageDriver();

            return new MediaService(
                $storage,
                new TempFileUploader(),
                $this->getInterventionManager(),
                new ImageTransformer
            );
        });

        $this->mergeConfigFrom(__DIR__.'/../config/easymutators.php', 'easymutators');
    }

    protected function getInterventionManager()
    {
        return new ImageManager([
            'driver' => $this->app['config']->get('easymutators.image_driver')
        ]);
    }

    protected function getStorageDriver()
    {
        $disk = $this->app['config']->get('easymutators.storage_disk');

        return $this->app['filesystem']->disk($disk);
    }

}