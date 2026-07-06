<?php

namespace TomShaw\Dropbox\Resources;

class DropboxAuth extends DropboxResource
{
    public function revokeToken(): ?array
    {
        return $this->client->rpc('auth/token/revoke');
    }
}
