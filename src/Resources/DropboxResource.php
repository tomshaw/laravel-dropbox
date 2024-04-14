<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\DropboxClient;

class DropboxResource
{
    public function __construct(
        protected DropboxClient $client
    ) {
    }

    protected function getRequestHeaders(bool $authTypeBasic = false): array
    {
        return array_merge($this->client->getContentType(), $this->client->getAuthorizationHeader($authTypeBasic));
    }
}
