<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\Enums\Endpoints;

class DropboxFiles extends DropboxResource
{
    public function createFolder(string $path, bool $autorename = false): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/create_folder', [
            'path' => $path,
            'autorename' => $autorename,
        ], headers: $this->getRequestHeaders());
    }

    public function listFolder(string $path = ''): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/list_folder', [
            'path' => $path,
        ], headers: $this->getRequestHeaders());
    }

    public function listContentsContinue(string $cursor = ''): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/list_folder/continue', [
            'cursor' => $cursor,
        ], headers: $this->getRequestHeaders());
    }

    public function move(string $fromPath, string $toPath, bool $autoRename = false, bool $allowOwnershipTransfer = false): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/move_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autoRename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ], headers: $this->getRequestHeaders());
    }

    public function delete(string $path): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/delete_v2', [
            'path' => $path,
        ], headers: $this->getRequestHeaders());
    }

    public function search(string $query, string $path = '', int $maxResults = 100, bool $includeHighlights = false): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/search_v2', [
            'query' => $query,
            'path' => $path,
            'max_results' => $maxResults,
            'include_highlights' => $includeHighlights,
        ], headers: $this->getRequestHeaders());
    }

    public function getMetadata(string $path): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/get_metadata', [
            'path' => $path,
        ], headers: $this->getRequestHeaders());
    }

    public function getTemporaryLink(string $path): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/get_temporary_link', [
            'path' => $path,
        ], headers: $this->getRequestHeaders());
    }

    public function getThumbnail(string $path): ?array
    {
        return $this->client->post(Endpoints::Base->value.'files/get_thumbnail', [
            'path' => $path,
        ], headers: $this->getRequestHeaders());
    }

    public function upload(string $path, $contents, $mode = 'add', bool $autorename = false, bool $mute = false, bool $strictConflict = false): ?array
    {
        $arguments = [
            'path' => $path,
            'mode' => $mode,
            'autorename' => $autorename,
            'mute' => $mute,
            'strict_conflict' => $strictConflict,
        ];

        return $this->client->post(Endpoints::Content->value.'files/upload', body: $contents, headers: array_merge($this->getArgumentHeaders($arguments), $this->getRequestHeaders(contentType: 'application/octet-stream')));
    }

    public function download(string $path): ?array
    {
        return $this->client->post(Endpoints::Content->value.'files/download', [
            'path' => $path,
        ], headers: $this->getRequestHeaders());
    }
}
