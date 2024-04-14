<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxAuth extends DropboxResource
{
    public function revokeToken(): ?array
    {
        return $this->client->post(Endpoints::Base->value.'auth/token/revoke', headers: $this->getRequestHeaders());
    }
}
