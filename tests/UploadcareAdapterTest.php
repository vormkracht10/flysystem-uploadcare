<?php

use Illuminate\Http\UploadedFile;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use Uploadcare\Api;
use Uploadcare\Configuration;
use Vormkracht10\UploadcareAdapter\UploadcareAdapter;


beforeEach(function () {
    $this->api = new Api(Configuration::create('demopublickey', 'demosecretkey'));

    $this->uploadcareAdapter = new UploadcareAdapter($this->api, []);
});

it('writes and returns uuid', function () {
    $uuid = $this->uploadcareAdapter->writeGetUuid('filename.txt', 'content', new Config());

    expect($uuid)->toBeString();
});

it('does find existing files', function () {
    $uuid = $this->uploadcareAdapter->writeGetUuid('filename.txt', 'content', new Config());

    $exists = $this->uploadcareAdapter->fileExists($uuid);

    expect($exists)->toBeTrue();
});

it('does not find invalid files', function () {
    $exists = $this->uploadcareAdapter->fileExists('this-does-not-exists');

    expect($exists)->toBeFalse();
});

it('writes streams and returns uuid', function() {
    $uuid = $this->uploadcareAdapter->writeStreamGetUuid('filename.txt', tmpfile(), new Config());
    
    expect($uuid)->toBeString();
});

it('writes uploadedfile and returns uuid', function() {
    $uploadedFile = new UploadedFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'dummy.txt', 'dummy.txt');
    $uuid = $this->uploadcareAdapter->putGetUuid('filename.txt', $uploadedFile);

    expect($uuid)->toBeString();
});

it('does get information of a file', function () {
    $uuid = $this->uploadcareAdapter->writeGetUuid('filename.txt', 'content', new Config());

    $fileInfo = $this->uploadcareAdapter->getFileinfo($uuid);

    expect($fileInfo)->toBeInstanceOf(FileAttributes::class);
});

it('does list files', function () {
    $files = $this->uploadcareAdapter->listContents();

    expect($files)->toBeIterable();
});