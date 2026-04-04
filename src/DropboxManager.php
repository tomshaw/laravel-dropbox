<?php

namespace TomShaw\Dropbox;

use TomShaw\Dropbox\Resources\{DropboxAuth, DropboxCheck, DropboxFiles, DropboxSharing, DropboxUsers};

class DropboxManager
{
    public function getAuthUrl(): string
    {
        return app(DropboxClient::class)->getAuthUrl();
    }

    public function revoke(): bool
    {
        $this->auth()->revokeToken();

        return app(DropboxClient::class)->deleteAccessToken();
    }

    public function connect(string $code)
    {
        $client = app(DropboxClient::class);

        $accessToken = $client->getAccessTokenWithAuthCode($code);

        return $client->setAccessToken($accessToken);
    }

    public function auth(): DropboxAuth
    {
        return new DropboxAuth(app(DropboxClient::class));
    }

    public function check(): DropboxCheck
    {
        return new DropboxCheck(app(DropboxClient::class));
    }

    public function users(): DropboxUsers
    {
        return new DropboxUsers(app(DropboxClient::class));
    }

    public function files(): DropboxFiles
    {
        return new DropboxFiles(app(DropboxClient::class));
    }

    public function sharing(): DropboxSharing
    {
        return new DropboxSharing(app(DropboxClient::class));
    }
}
