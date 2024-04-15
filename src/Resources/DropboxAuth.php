<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxAuth extends DropboxResource
{
    public function revokeToken(): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'auth/token/revoke');
    }
}
