<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxUsers extends DropboxResource
{
    public function getCurrentAccount(): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'users/get_current_account');
    }
}
