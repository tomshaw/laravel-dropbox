<?php

namespace TomShaw\Dropbox\Storage;

interface StorageAdapterInterface
{
    /**
     * @param  array<string, mixed>  $accessToken
     */
    public function set(array $accessToken): void;

    /**
     * @return array<string, mixed>|null
     */
    public function get(): ?array;

    public function delete(): bool;
}
