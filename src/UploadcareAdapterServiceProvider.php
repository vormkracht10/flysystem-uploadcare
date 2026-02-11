<?php

namespace Vormkracht10\UploadcareAdapter;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class UploadcareAdapterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('uploadcare', function ($app, $config) {
            $configuration = \Uploadcare\Configuration::create($config['public'], $config['secret']);
            $api = new \Uploadcare\Api($configuration);

            $adapter = new UploadcareAdapter($api, $config);

            return new UploadcareFilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
