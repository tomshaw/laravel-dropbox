<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxSharing extends DropboxResource
{
    public function createSharedLinkWithSettings(string $path, array $settings = []): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'sharing/create_shared_link_with_settings', [
            'path' => $path,
            'settings' => $settings,
        ]);
    }
}
