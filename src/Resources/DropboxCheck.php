<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxCheck extends DropboxResource
{
    public function app(array $options = []): ?array
    {
        return $this->client->post(Endpoints::Base->value.'check/app', body: $options, headers: $this->getRequestHeaders(true));
    }

    public function user($options = []): ?array
    {
        return $this->client->post(Endpoints::Base->value.'check/user', body: $options, headers: $this->getRequestHeaders(true));
    }
}
