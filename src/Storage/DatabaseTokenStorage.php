<?php

namespace TomShaw\Dropbox\Storage;

use TomShaw\Dropbox\Models\DropboxToken;

class DatabaseTokenStorage implements StorageAdapterInterface
{
    public function set(array $accessToken): self
    {
        DropboxToken::updateOrCreate(['user_id' => auth()->id()], array_merge(['user_id' => auth()->id()], $accessToken));

        return $this;
    }

    public function get(): ?DropboxToken
    {
        return DropboxToken::where('user_id', auth()->id())->first();
    }

    public function delete(): int
    {
        return DropboxToken::where('user_id', auth()->id())->delete();
    }
}
