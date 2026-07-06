<?php

namespace TomShaw\Dropbox;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getAuthUrl()
 * @method static DropboxClient connect(string $code, ?string $state = null)
 * @method static bool revoke()
 * @method static DropboxClient client()
 * @method static \TomShaw\Dropbox\Resources\DropboxAuth auth()
 * @method static \TomShaw\Dropbox\Resources\DropboxCheck check()
 * @method static \TomShaw\Dropbox\Resources\DropboxUsers users()
 * @method static \TomShaw\Dropbox\Resources\DropboxFiles files()
 * @method static \TomShaw\Dropbox\Resources\DropboxSharing sharing()
 *
 * @see DropboxManager
 */
class Dropbox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DropboxManager::class;
    }
}
