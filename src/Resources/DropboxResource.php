<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\DropboxClient;

class DropboxResource
{
    public function __construct(
        protected DropboxClient $client
    ) {
    }

    protected function getRequestHeaders(bool $authTypeBasic = false, string $contentType = 'application/json'): array
    {
        return array_merge($this->client->getContentType($contentType), $this->client->getAuthorizationHeader($authTypeBasic));
    }

    protected function getArgumentHeaders(array $arguments = []): array
    {
        return ['Dropbox-API-Arg' => json_encode($arguments)];
    }
}
