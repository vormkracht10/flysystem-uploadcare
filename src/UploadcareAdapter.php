<?php

namespace Vormkracht10\UploadcareAdapter;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use \League\Flysystem\FilesystemAdapter;

class UploadcareAdapter implements FilesystemAdapter
{
    /** @var \Uploadcare\Api */
    protected $api;
    protected $cdn = 'https://ucarecdn.com';

    /**
     * Create the adapter with access to Uploadcare's api.
     *
     * @param  \Uploadcare\Api  $api
     */
    public function __construct(\Uploadcare\Api $api, ?string $cdn = null)
    {
        $this->api = $api;
        if ($cdn) {
            $this->cdn = $cdn;
        }
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

        $url = $this->cdn  . '/' . $path . '/';

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

        $url = $this->cdn . '/' . $path . '/';

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
