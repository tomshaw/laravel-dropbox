<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxSharing extends DropboxResource
{
    public function createSharedLink(string $path, bool $shortUrl = false, bool $pendingUpload = false): ?array
    {
        return $this->client->post(Endpoints::Base->value.'sharing/create_shared_link', [
            'path' => $path,
            'short_url' => $shortUrl,
            'pending_upload' => $pendingUpload,
        ], headers: $this->getRequestHeaders());
    }

    public function createSharedLinkWithSettings(string $path, array $settings = []): ?array
    {
        return $this->client->post(Endpoints::Base->value.'sharing/create_shared_link_with_settings', [
            'path' => $path,
            'settings' => $settings,
        ], headers: $this->getRequestHeaders());
    }
}
