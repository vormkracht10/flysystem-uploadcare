<?php

namespace Vormkracht10\UploadcareAdapter;

use ErrorException;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\File;
use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InvalidVisibilityProvided;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use Psr\Http\Message\StreamInterface;
use Uploadcare\Exception\HttpException;

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

    /**
     * Create the adapter with access to Uploadcare's api.
     */
    public function __construct(\Uploadcare\Api $api, ?array $config = [])
    {
        $this->api = $api;
        $this->config = $config;
    }

    /**
     * Get the cdn
     */
    protected function getCdn(): string
    {
        return (string) ($this->config['cdn'] ?? 'https://ucarecdn.com');
    }

    /**
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
            throw new UnableToCheckExistence($e->getMessage());
        }

        return true;
    }

    /**
     * @throws UnableToCheckExistence
     */
    public function directoryExists(string $path): bool
    {
        try {
            $this->api->group()->groupInfo($path);
        } catch (\Uploadcare\Exception\HttpException $e) {
            if ($e->getCode() == 404) {
                return false;
            }
            throw new UnableToCheckExistence($e->getMessage());
        }

        return true;
    }

    /**
     * @throws UnableToWriteFile
     */
    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $this->api->uploader()->fromContent(
                content: $contents,
                filename: $path
            );
        }
        catch (InvalidArgumentException $e) {
            throw new UnableToWriteFile($e->getMessage());
        }
    }

    /**
     * @throws UnableToWriteFile
     */
    public function writeGetUuid(string $path, string $contents, $config): string|bool
    {
        try {
            $result = $this->api->uploader()->fromContent(
                content: $contents,
                filename: $path
            );
        }
        catch (InvalidArgumentException $e) {
            throw new UnableToWriteFile($e->getMessage());
        }

        return $result->getUuid();
    }

    /**
     * @param  resource  $contents
     *
     * @throws UnableToWriteFile
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        try {
            $this->api->uploader()->fromResource(
                handle: $contents,
                filename: $path
            );
        }
        catch (InvalidArgumentException $e) {
            throw new UnableToWriteFile($e->getMessage());
        }
    }

    /**
     * @param  resource  $contents
     *
     * @throws UnableToWriteFile
     */
    public function writeStreamGetUuid(string $path, $contents, $config): string|bool
    {
        try {
            $result = $this->api->uploader()->fromResource(
                handle: $contents,
                filename: $path
            );
        }
        catch (InvalidArgumentException $e) {
            throw new UnableToWriteFile($e->getMessage());
        }

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

        if ($contents instanceof StreamInterface) {
            return $this->writeStreamGetUuid($path, $contents->detach(), $options);
        }

        $uuid = is_resource($contents)
            ? $this->writeStreamGetUuid($path, $contents, $options)
            : $this->writeGetUuid($path, $contents, $options);

        return $uuid;
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
     * {@inheritdoc}
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
     */
    public function read(string $path): string
    {
        $url = $this->getCdn().'/'.$path.'/';

        try {
            $content = file_get_contents($url);
        }
        catch (ErrorException $e) {
            throw new UnableToReadFile($e->getMessage());
        }

        return $content;
    }

    /**
     * @return resource
     *
     * @throws UnableToReadFile
     */
    public function readStream(string $path)
    {
        $url = $this->getCdn().'/'.$path.'/';

        try {
            $stream = fopen($url, 'rb');
        }
        catch (ErrorException $e) {
            throw new UnableToReadFile($e->getMessage());
        }

        return $stream;
    }

    /**
     * @throws UnableToDeleteFile
     */
    public function delete(string $path): void
    {
        try {
            $this->api->file()->deleteFile($path);
        }
        catch (HttpException $e) {
            throw new UnableToDeleteFile($e->getMessage());
        }
    }

    /**
     * @throws UnableToDeleteDirectory
     */
    public function deleteDirectory(string $path): void
    {
        try {
            $this->api->group()->removeGroup($path);
        } catch (\Uploadcare\Exception\HttpException $e) {
            throw new UnableToDeleteDirectory($e->getMessage());
        }
    }

    /**
     * @throws UnableToCreateDirectory
     */
    public function createDirectory(string $path, Config $config): void
    {
        throw new UnableToCreateDirectory('Unable to create group');
    }

    /**
     * @throws InvalidVisibilityProvided
     */
    public function setVisibility(string $path, string $visibility): void
    {
        throw new InvalidVisibilityProvided();
    }

    /**
     * @throws UnableToRetrieveMetadata
     */
    public function visibility(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    public function getFileinfo(string $path): FileAttributes
    {
        try {
            $info = $this->api->file()->fileInfo($path);
        }
        catch (\Exception $e) {
            throw new UnableToRetrieveMetadata($e->getMessage());
        }

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
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    /**
     * @throws UnableToRetrieveMetadata
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    /**
     * @throws UnableToRetrieveMetadata
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->getFileinfo($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $result = $this->api->file()->listFiles();

        return $result->getResults();
    }

    /**
     * @throws UnableToMoveFile
     */
    public function move(string $source, string $destination, Config $config): void
    {
        throw new UnableToMoveFile();
    }

    /**
     * @throws UnableToCopyFile
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        throw new UnableToCopyFile();
    }
}
