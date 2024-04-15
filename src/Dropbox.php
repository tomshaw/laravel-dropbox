<?php

namespace TomShaw\Dropbox;

use Illuminate\Support\Facades\Facade;

class Dropbox extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DropboxManager::class;
    }
}
