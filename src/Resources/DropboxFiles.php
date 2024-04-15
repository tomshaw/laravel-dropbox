<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxFiles extends DropboxResource
{
    public function createFolder(string $path, bool $autorename = false): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/create_folder', [
            'path' => $path,
            'autorename' => $autorename,
        ]);
    }

    public function listFolder(string $path = ''): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/list_folder', [
            'path' => $path,
        ]);
    }

    public function listContentsContinue(string $cursor = ''): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/list_folder/continue', [
            'cursor' => $cursor,
        ]);
    }

    public function copy(string $fromPath, string $toPath, bool $autoRename = false, bool $allowOwnershipTransfer = false): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/copy_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autoRename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    public function move(string $fromPath, string $toPath, bool $autoRename = false, bool $allowOwnershipTransfer = false): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/move_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autoRename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    public function delete(string $path): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/delete_v2', [
            'path' => $path,
        ]);
    }

    public function search(string $query, string $path = '', int $maxResults = 100, bool $includeHighlights = false): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/search_v2', [
            'query' => $query,
            'path' => $path,
            'max_results' => $maxResults,
            'include_highlights' => $includeHighlights,
        ]);
    }

    public function getMetadata(string $path): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/get_metadata', [
            'path' => $path,
        ]);
    }

    public function getTemporaryLink(string $path): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/get_temporary_link', [
            'path' => $path,
        ]);
    }

    public function getThumbnail(string $path): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Base->value.'files/get_thumbnail', [
            'path' => $path,
        ]);
    }

    public function upload(string $path, mixed $contents, $mode = 'add', bool $autorename = false, bool $mute = false, bool $strictConflict = false): ?array
    {
        if (! is_resource($contents)) {
            throw new \InvalidArgumentException('Contents must be a valid resource');
        }

        $arguments = [
            'path' => $path,
            'mode' => $mode,
            'autorename' => $autorename,
            'mute' => $mute,
            'strict_conflict' => $strictConflict,
        ];

        $this->client->headers(bearer: true, contentType: 'application/octet-stream', arguments: $arguments);

        return $this->client->post(Endpoints::Content->value.'files/upload', contents: $contents);
    }

    public function download(string $path): ?array
    {
        $this->client->headers(bearer: true);

        return $this->client->post(Endpoints::Content->value.'files/download', [
            'path' => $path,
        ]);
    }
}
