<?php

namespace Vormkracht10\UploadcareAdapter;

use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\File;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use \League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use Psr\Http\Message\StreamInterface;

class UploadcareAdapter implements FilesystemAdapter
{
    /** @var \Uploadcare\Api */
    protected $api;
    /**
     * The filesystem configuration.
     *
     * @var array
     */
    protected $config;


    protected static $macros = [
        'putGetUuid'
    ];

    /**
     * Create the adapter with access to Uploadcare's api.
     *
     * @param  \Uploadcare\Api  $api
     */
    public function __construct(\Uploadcare\Api $api, ?array $config = [])
    {
        $this->api = $api;
        $this->config = $config;
    }

    /**
     * Determine if Flysystem exceptions should be thrown.
     *
     * @return bool
     */
    protected function throwsExceptions(): bool
    {
        return (bool) ($this->config['throw'] ?? false);
    }

    /**
     * Get the cdn
     * 
     * @return string
     */
    protected function getCdn(): string
    {
        return (string) ($this->config['cdn'] ?? 'https://ucarecdn.com');
    }

    /**
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function fileExists(string $path): bool
    {
        try {
            $this->api->file()->fileInfo($path);
        } catch (\Uploadcare\Exception\HttpException $e) {
            if ($e->getCode() == 404) {
                return false;
            }
            throw $e;
        }
        return true;
    }

    /**
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function directoryExists(string $path): bool
    {
        return true;
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->api->uploader()->fromContent(
            content: $contents,
            filename: $path
        );
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeGetUuid(string $path, string $contents, $config): string
    {
        $result = $this->api->uploader()->fromContent(
            content: $contents,
            filename: $path
        );

        return $result->getUuid();
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->api->uploader()->fromResource(
            handle: $contents,
            filename: $path
        );
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeStreamGetUuid(string $path, $contents, $config): string
    {
        $result = $this->api->uploader()->fromResource(
            handle: $contents,
            filename: $path
        );

        return $result->getUuid();
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  \Psr\Http\Message\StreamInterface|\Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|resource  $contents
     * @param  mixed  $options
     * @return string|bool
     */
    public function putGetUuid($path, $contents, $options = [])
    {
        $options = is_string($options)
                     ? ['visibility' => $options]
                     : (array) $options;

        if ($contents instanceof File ||
            $contents instanceof UploadedFile) {
            return $this->putFileGetUuid($path, $contents, $options);
        }

        try {
            if ($contents instanceof StreamInterface) {
                return $this->writeStreamGetUuid($path, $contents->detach(), $options);
            }

            $uuid = is_resource($contents)
                ? $this->writeStreamGetUuid($path, $contents, $options)
                : $this->writeGetUuid($path, $contents, $options);

            return $uuid;

        } catch (UnableToWriteFile|UnableToSetVisibility $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    public function putFileGetUuid($path, $file = null, $options = [])
    {
        if (is_null($file) || is_array($file)) {
            [$path, $file, $options] = ['', $path, $file ?? []];
        }

        $file = is_string($file) ? new File($file) : $file;

        return $this->putFileAsGetUuid($path, $file, $file->hashName(), $options);
    }

    /**
     * Store the uploaded file on the disk with a given name.
     *
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|array|null  $file
     * @param  string|array|null  $name
     * @param  mixed  $options
     * @return string|false
     */
    public function putFileAsGetUuid($path, $file, $name = null, $options = [])
    {
        if (is_null($name) || is_array($name)) {
            [$path, $file, $name, $options] = ['', $path, $file, $name ?? []];
        }

        $stream = fopen(is_string($file) ? $file : $file->getRealPath(), 'r');

        $uuid = $this->putGetUuid(
            $path = trim($path.'/'.$name, '/'), $stream, $options
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $uuid;
    }

    /**
     * @inheritdoc
     */
    public function update($path, $contents, Config $config)
    {
        $this->api->uploader()->fromContent(
            content: $contents,
            filename: $path
        );
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function read(string $path): string
    {

        $url = $this->getCdn()  . '/' . $path . '/';

        return file_get_contents($url);
    }
    /**
     * @return resource
     *
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function readStream(string $path)
    {

        $url = $this->getCdn() . '/' . $path . '/';

        return fopen($url, 'rb');
    }

    /**
     * @throws UnableToDeleteFile
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        $this->api->file()->deleteFile($path);
    }

    /**
     * @throws UnableToDeleteDirectory
     * @throws FilesystemException
     */
    public function deleteDirectory(string $path): void
    {
    }

    /**
     * @throws UnableToCreateDirectory
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void
    {
    }

    /**
     * @throws InvalidVisibilityProvided
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes
    {
    }

    public function getFileinfo(string $path): FileAttributes
    {

        $info = $this->api->file()->fileInfo($path);

        return new FileAttributes(
            path: $info->getOriginalFilename(),
            fileSize: $info->getSize(),
            lastModified: strtotime($info->getDatetimeStored()->format('Y-m-d H:i:s')),
            mimeType: $info->getMimeType(),
            extraMetadata: (array) $info->getMetadata()
        );
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    /**
     * @return iterable<StorageAttributes>
     *
     * @throws FilesystemException
     */
    public function listContents(string $path, bool $deep): iterable
    {
    }

    /**
     * @throws UnableToMoveFile
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, Config $config): void
    {
    }

    /**
     * @throws UnableToCopyFile
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
    }
}
