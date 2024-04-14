<?php

namespace TomShaw\Dropbox;

use Illuminate\Support\Facades\Facade;

/**
 * @see DropboxManager
 */
class Dropbox extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DropboxManager::class;
    }
}
