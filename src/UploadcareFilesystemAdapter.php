<?php

namespace Vormkracht10\UploadcareAdapter;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\FileAttributes;

class UploadcareFilesystemAdapter extends FilesystemAdapter
{
    protected UploadcareAdapter $uploadcareAdapter;

    public function __construct(
        \League\Flysystem\FilesystemOperator $driver,
        UploadcareAdapter $adapter,
        array $config = [],
    ) {
        $this->uploadcareAdapter = $adapter;

        parent::__construct($driver, $adapter, $config);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  \Psr\Http\Message\StreamInterface|\Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|resource  $contents
     * @param  mixed  $options
     * @return string|bool
     */
    public function put($path, $contents, $options = [])
    {
        return $this->uploadcareAdapter->putGetUuid($path, $contents, $options);
    }

    /**
     * @return string|bool
     */
    public function putGetUuid(string $path, mixed $contents, mixed $options = [])
    {
        return $this->uploadcareAdapter->putGetUuid($path, $contents, $options);
    }

    /**
     * @return string|bool
     */
    public function putFileGetUuid(string $path, mixed $contents, mixed $options = [])
    {
        return $this->uploadcareAdapter->putFileGetUuid($path, $contents, $options);
    }

    /**
     * @return string|false
     */
    public function putFileAsGetUuid(string $path, mixed $file, ?string $name = null, array $options = [])
    {
        return $this->uploadcareAdapter->putFileAsGetUuid($path, $file, $name, $options);
    }

    public function fileInfo(string $path): FileAttributes
    {
        return $this->uploadcareAdapter->getFileinfo($path);
    }
}
