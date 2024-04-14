<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxUsers extends DropboxResource
{
    public function getCurrentAccount(): ?array
    {
        return $this->client->post(Endpoints::Base->value.'users/get_current_account', headers: $this->getRequestHeaders());
    }
}
