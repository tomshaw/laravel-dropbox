<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxCheck extends DropboxResource
{
    public function app(array $body = []): ?array
    {
        $this->client->headers(basic: true);

        return $this->client->post(Endpoints::Base->value.'check/app', body: $body);
    }

    public function user(array $body = []): ?array
    {
        $this->client->headers(basic: true);

        return $this->client->post(Endpoints::Base->value.'check/user', body: $body);
    }
}
