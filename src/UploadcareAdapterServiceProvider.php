<?php

namespace Vormkracht10\UploadcareAdapter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class UploadcareAdapterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('uploadcare', function ($app, $config) {
            $configuration = \Uploadcare\Configuration::create($config['public_key'], $config['secret_key']);
            $api = new \Uploadcare\Api($configuration);

            $adapter = new UploadcareAdapter($api);

            return new FilesystemAdapter(
                new Filesystem($adapter),
                $adapter,
                $config
            );
        });
    }
}
