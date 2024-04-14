<?php

namespace TomShaw\Dropbox;

use TomShaw\Dropbox\Resources\{DropboxAuth, DropboxCheck, DropboxFiles, DropboxSharing, DropboxUsers};

class DropboxManager
{
    public function auth(DropboxClient $client): DropboxAuth
    {
        return new DropboxAuth($client);
    }

    public function check(DropboxClient $client): DropboxCheck
    {
        return new DropboxCheck($client);
    }

    public function users(DropboxClient $client): DropboxUsers
    {
        return new DropboxUsers($client);
    }

    public function files(DropboxClient $client): DropboxFiles
    {
        return new DropboxFiles($client);
    }

    public function sharing(DropboxClient $client): DropboxSharing
    {
        return new DropboxSharing($client);
    }
}
