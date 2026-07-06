<?php

namespace TomShaw\Dropbox\Resources;

class DropboxCheck extends DropboxResource
{
    /**
     * Verify the app key and secret using app (basic) authentication.
     *
     * @return array<string, mixed>|null
     */
    public function app(string $query = 'ping'): ?array
    {
        return $this->client->appCheck('check/app', [
            'query' => $query,
        ]);
    }

    /**
     * Verify the stored user access token.
     *
     * @return array<string, mixed>|null
     */
    public function user(string $query = 'ping'): ?array
    {
        return $this->client->rpc('check/user', [
            'query' => $query,
        ]);
    }
}
