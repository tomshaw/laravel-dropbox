<?php

namespace TomShaw\Dropbox\Resources;

class DropboxAuth extends DropboxResource
{
    /**
     * @return array<string, mixed>|null
     */
    public function revokeToken(): ?array
    {
        return $this->client->rpc('auth/token/revoke');
    }
}
