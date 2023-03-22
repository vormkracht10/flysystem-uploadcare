<?php

namespace Vormkracht10\UploadcareAdapter;
use \League\Flysystem\FilesystemAdapter;

class UploadcareAdapter implements FilesystemAdapter
{
    /** @var \Uploadcare\Api */
    protected $api;

    /**
     * Create the adapter with access to Uploadcare's api.
     *
     * @param  \Uploadcare\Api  $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

}