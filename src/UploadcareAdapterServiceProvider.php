<?php

namespace Vormkracht10\UploadcareAdapter;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;

class UploadcareAdapterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('uploadcare', function ($app, $config) {

            $configuration = \Uploadcare\Configuration::create($config['public_key'], $config['secret_key']);
            $api = new \Uploadcare\Api($configuration);

            $adapter = new UploadcareAdapter($api, $config['cdn'] ?? null);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
