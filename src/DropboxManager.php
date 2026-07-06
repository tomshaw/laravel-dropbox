<?php

namespace TomShaw\Dropbox;

use TomShaw\Dropbox\Resources\{DropboxAuth, DropboxCheck, DropboxFiles, DropboxSharing, DropboxUsers};

class DropboxManager
{
    public function __construct(
        protected readonly DropboxClient $client
    ) {}

    public function client(): DropboxClient
    {
        return $this->client;
    }

    public function getAuthUrl(): string
    {
        return $this->client->getAuthUrl();
    }

    public function connect(string $code, ?string $state = null): DropboxClient
    {
        return $this->client->setAccessToken(
            $this->client->getAccessTokenWithAuthCode($code, $state)
        );
    }

    public function revoke(): bool
    {
        $this->auth()->revokeToken();

        return $this->client->deleteAccessToken();
    }

    public function auth(): DropboxAuth
    {
        return new DropboxAuth($this->client);
    }

    public function check(): DropboxCheck
    {
        return new DropboxCheck($this->client);
    }

    public function users(): DropboxUsers
    {
        return new DropboxUsers($this->client);
    }

    public function files(): DropboxFiles
    {
        return new DropboxFiles($this->client);
    }

    public function sharing(): DropboxSharing
    {
        return new DropboxSharing($this->client);
    }
}
