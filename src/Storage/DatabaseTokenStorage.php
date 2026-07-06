<?php

namespace TomShaw\Dropbox\Storage;

use TomShaw\Dropbox\Exceptions\DropboxException;
use TomShaw\Dropbox\Models\DropboxToken;

class DatabaseTokenStorage implements StorageAdapterInterface
{
    public function set(array $accessToken): void
    {
        $userId = auth()->id() ?? throw new DropboxException('Cannot store a Dropbox token without an authenticated user.');

        DropboxToken::query()->updateOrCreate(['user_id' => $userId], $accessToken);
    }

    public function get(): ?array
    {
        $userId = auth()->id();

        if ($userId === null) {
            return null;
        }

        return DropboxToken::query()->where('user_id', $userId)->first()?->only([
            'access_token',
            'refresh_token',
            'expires_at',
            'token_type',
            'uid',
            'account_id',
            'scope',
        ]);
    }

    public function delete(): bool
    {
        return DropboxToken::query()->where('user_id', auth()->id())->delete() > 0;
    }
}
