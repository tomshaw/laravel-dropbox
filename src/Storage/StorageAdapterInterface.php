<?php

namespace TomShaw\Dropbox\Storage;

// use TomShaw\Dropbox\Models\DropboxToken;

interface StorageAdapterInterface
{
    public function set(array $accessToken): self;

    public function get(): mixed;

    public function delete(): mixed;
}
