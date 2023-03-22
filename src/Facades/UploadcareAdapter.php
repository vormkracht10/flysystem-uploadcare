<?php

namespace Vormkracht10\UploadcareAdapter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\UploadcareAdapter\UploadcareAdapter
 */
class UploadcareAdapter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\UploadcareAdapter\UploadcareAdapter::class;
    }
}
