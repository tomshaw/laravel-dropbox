<?php

namespace TomShaw\Dropbox\Resources;

class DropboxSharing extends DropboxResource
{
    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>|null
     */
    public function createSharedLinkWithSettings(string $path, array $settings = []): ?array
    {
        $body = ['path' => $path];

        if ($settings !== []) {
            $body['settings'] = $settings;
        }

        return $this->client->rpc('sharing/create_shared_link_with_settings', $body);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function listSharedLinks(?string $path = null, ?string $cursor = null, bool $directOnly = false): ?array
    {
        return $this->client->rpc('sharing/list_shared_links', array_filter([
            'path' => $path,
            'cursor' => $cursor,
            'direct_only' => $directOnly,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function revokeSharedLink(string $url): ?array
    {
        return $this->client->rpc('sharing/revoke_shared_link', [
            'url' => $url,
        ]);
    }
}
