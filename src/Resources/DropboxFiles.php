<?php

namespace TomShaw\Dropbox\Resources;

use InvalidArgumentException;
use TomShaw\Dropbox\Enums\WriteMode;

class DropboxFiles extends DropboxResource
{
    /**
     * Dropbox rejects single-request uploads larger than 150 MB.
     */
    public const MAX_SINGLE_UPLOAD_BYTES = 150 * 1024 * 1024;

    public const DEFAULT_CHUNK_BYTES = 48 * 1024 * 1024;

    public function createFolder(string $path, bool $autorename = false): ?array
    {
        return $this->client->rpc('files/create_folder_v2', [
            'path' => $path,
            'autorename' => $autorename,
        ]);
    }

    public function listFolder(string $path = '', bool $recursive = false, bool $includeDeleted = false, ?int $limit = null, bool $includeMediaInfo = false): ?array
    {
        return $this->client->rpc('files/list_folder', array_filter([
            'path' => $path,
            'recursive' => $recursive,
            'include_deleted' => $includeDeleted,
            'include_media_info' => $includeMediaInfo,
            'limit' => $limit,
        ], fn (mixed $value): bool => $value !== null));
    }

    public function listFolderContinue(string $cursor): ?array
    {
        return $this->client->rpc('files/list_folder/continue', [
            'cursor' => $cursor,
        ]);
    }

    public function copy(string $fromPath, string $toPath, bool $autorename = false, bool $allowOwnershipTransfer = false): ?array
    {
        return $this->client->rpc('files/copy_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autorename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    public function move(string $fromPath, string $toPath, bool $autorename = false, bool $allowOwnershipTransfer = false): ?array
    {
        return $this->client->rpc('files/move_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autorename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    public function delete(string $path): ?array
    {
        return $this->client->rpc('files/delete_v2', [
            'path' => $path,
        ]);
    }

    /**
     * @param  array<int, string>  $paths
     */
    public function deleteBatch(array $paths): ?array
    {
        return $this->client->rpc('files/delete_batch', [
            'entries' => array_map(fn (string $path): array => ['path' => $path], array_values($paths)),
        ]);
    }

    public function deleteBatchCheck(string $asyncJobId): ?array
    {
        return $this->client->rpc('files/delete_batch/check', [
            'async_job_id' => $asyncJobId,
        ]);
    }

    public function permanentlyDelete(string $path): ?array
    {
        return $this->client->rpc('files/permanently_delete', [
            'path' => $path,
        ]);
    }

    public function search(string $query, string $path = '', int $maxResults = 100, bool $includeHighlights = false, bool $filenameOnly = false): ?array
    {
        $options = [
            'max_results' => $maxResults,
            'filename_only' => $filenameOnly,
        ];

        if ($path !== '') {
            $options['path'] = $path;
        }

        return $this->client->rpc('files/search_v2', [
            'query' => $query,
            'options' => $options,
            'match_field_options' => [
                'include_highlights' => $includeHighlights,
            ],
        ]);
    }

    public function searchContinue(string $cursor): ?array
    {
        return $this->client->rpc('files/search/continue_v2', [
            'cursor' => $cursor,
        ]);
    }

    public function getMetadata(string $path, bool $includeDeleted = false, bool $includeMediaInfo = false): ?array
    {
        return $this->client->rpc('files/get_metadata', [
            'path' => $path,
            'include_deleted' => $includeDeleted,
            'include_media_info' => $includeMediaInfo,
        ]);
    }

    public function getTemporaryLink(string $path): ?array
    {
        return $this->client->rpc('files/get_temporary_link', [
            'path' => $path,
        ]);
    }

    public function listRevisions(string $path, int $limit = 10): ?array
    {
        return $this->client->rpc('files/list_revisions', [
            'path' => $path,
            'limit' => $limit,
        ]);
    }

    public function restore(string $path, string $rev): ?array
    {
        return $this->client->rpc('files/restore', [
            'path' => $path,
            'rev' => $rev,
        ]);
    }

    public function saveUrl(string $path, string $url): ?array
    {
        return $this->client->rpc('files/save_url', [
            'path' => $path,
            'url' => $url,
        ]);
    }

    public function saveUrlCheckJobStatus(string $asyncJobId): ?array
    {
        return $this->client->rpc('files/save_url/check_job_status', [
            'async_job_id' => $asyncJobId,
        ]);
    }

    /**
     * Returns the raw thumbnail image bytes.
     */
    public function getThumbnail(string $path, string $format = 'jpeg', string $size = 'w64h64', string $mode = 'strict'): string
    {
        return $this->client->contentDownload('files/get_thumbnail_v2', [
            'resource' => ['.tag' => 'path', 'path' => $path],
            'format' => $format,
            'size' => $size,
            'mode' => $mode,
        ])->body();
    }

    /**
     * Returns the raw preview document bytes (PDF or HTML depending on source).
     */
    public function getPreview(string $path): string
    {
        return $this->client->contentDownload('files/get_preview', [
            'path' => $path,
        ])->body();
    }

    /**
     * Returns the raw exported file bytes.
     */
    public function export(string $path, ?string $exportFormat = null): string
    {
        return $this->client->contentDownload('files/export', array_filter([
            'path' => $path,
            'export_format' => $exportFormat,
        ], fn (mixed $value): bool => $value !== null))->body();
    }

    /**
     * Download a file into memory and return its contents.
     */
    public function download(string $path): string
    {
        return $this->client->contentDownload('files/download', [
            'path' => $path,
        ])->body();
    }

    /**
     * Stream a file directly to a local path without buffering it in memory.
     * Returns the file metadata from the Dropbox-API-Result header.
     */
    public function downloadTo(string $path, string $localPath): ?array
    {
        $response = $this->client->contentDownload('files/download', [
            'path' => $path,
        ], sink: $localPath);

        $result = $response->header('Dropbox-API-Result');

        return $result !== '' ? json_decode($result, true) : null;
    }

    /**
     * Upload a local file. Files above the 150 MB single-request limit are
     * automatically uploaded through an upload session.
     */
    public function upload(string $destinationPath, string $sourceFilePath, WriteMode $mode = WriteMode::Add, bool $autorename = false, bool $mute = false, bool $strictConflict = false, int $chunkSize = self::DEFAULT_CHUNK_BYTES): ?array
    {
        $size = $this->assertReadableFile($sourceFilePath);

        if ($size > self::MAX_SINGLE_UPLOAD_BYTES) {
            return $this->uploadSession($destinationPath, $sourceFilePath, $mode, $autorename, $mute, $strictConflict, $chunkSize);
        }

        $stream = $this->openFile($sourceFilePath);

        try {
            return $this->client->contentUpload('files/upload', $this->commitArguments($destinationPath, $mode, $autorename, $mute, $strictConflict), $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Upload a local file of any size through an upload session, sending it
     * in sequential chunks.
     */
    public function uploadSession(string $destinationPath, string $sourceFilePath, WriteMode $mode = WriteMode::Add, bool $autorename = false, bool $mute = false, bool $strictConflict = false, int $chunkSize = self::DEFAULT_CHUNK_BYTES): ?array
    {
        $this->assertReadableFile($sourceFilePath);

        $stream = $this->openFile($sourceFilePath);

        try {
            $chunk = (string) fread($stream, $chunkSize);

            $session = $this->client->contentUpload('upload_session/start', ['close' => false], $chunk);

            $sessionId = $session['session_id'] ?? throw new InvalidArgumentException('Dropbox did not return an upload session id.');

            $offset = strlen($chunk);

            while (! feof($stream)) {
                $chunk = fread($stream, $chunkSize);

                if ($chunk === false || $chunk === '') {
                    break;
                }

                $this->client->contentUpload('upload_session/append_v2', [
                    'cursor' => ['session_id' => $sessionId, 'offset' => $offset],
                    'close' => false,
                ], $chunk);

                $offset += strlen($chunk);
            }

            return $this->client->contentUpload('upload_session/finish', [
                'cursor' => ['session_id' => $sessionId, 'offset' => $offset],
                'commit' => $this->commitArguments($destinationPath, $mode, $autorename, $mute, $strictConflict),
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function commitArguments(string $path, WriteMode $mode, bool $autorename, bool $mute, bool $strictConflict): array
    {
        return [
            'path' => $path,
            'mode' => $mode->value,
            'autorename' => $autorename,
            'mute' => $mute,
            'strict_conflict' => $strictConflict,
        ];
    }

    protected function assertReadableFile(string $sourceFilePath): int
    {
        if (! is_file($sourceFilePath)) {
            throw new InvalidArgumentException("Source file does not exist: {$sourceFilePath}");
        }

        $size = filesize($sourceFilePath);

        if ($size === false) {
            throw new InvalidArgumentException("Could not determine size of source file: {$sourceFilePath}");
        }

        return $size;
    }

    /**
     * @return resource
     */
    protected function openFile(string $sourceFilePath)
    {
        $stream = fopen($sourceFilePath, 'rb');

        if (! is_resource($stream)) {
            throw new InvalidArgumentException("Could not open source file for reading: {$sourceFilePath}");
        }

        return $stream;
    }
}
