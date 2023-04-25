<?php

namespace Vormkracht10\UploadcareAdapter;

use Illuminate\Filesystem\FilesystemAdapter;
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
            FilesystemAdapter::macro('putGetUuid', function (string $path, string $contents) use ($adapter) {
                return $adapter->putGetUuid($path, $contents);
            });
            FilesystemAdapter::macro('putFileGetUuid', function (string $path, $contents) use ($adapter) {
                return $adapter->putFileGetUuid($path, $contents);
            });
            FilesystemAdapter::macro('putFileAsGetUuid', function (string $path, $contents, $name, $options = []) use ($adapter) {
                return $adapter->putFileAsGetUuid($path, $contents, $name, $options);
            });
            FilesystemAdapter::macro('fileInfo', function (string $path) use ($adapter) {
                return $adapter->getFileinfo($path);
            });

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
