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

            $adapter = new UploadcareAdapter($api, $config);
            FilesystemAdapter::macro('putGetUuid', function (string $path, string $contents) {
                return Storage::disk('uploadcare')->getAdapter()->putGetUuid($path, $contents);
            });
            FilesystemAdapter::macro('putFileGetUuid', function (string $path, $contents) {
                return Storage::disk('uploadcare')->getAdapter()->putFileGetUuid($path, $contents);
            });
            // $path, $file, $name = null, $options = []
            FilesystemAdapter::macro('putFileAsGetUuid', function (string $path, $contents, $name, $options = []) {
                return Storage::disk('uploadcare')->getAdapter()->putFileAsGetUuid($path, $contents, $name, $options);
            });

            
            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
