<?php

namespace TomShaw\Dropbox\Storage;

class SessionTokenStorage implements StorageAdapterInterface
{
    public const SESSION_KEY = 'dropbox_token';

    public function set(array $accessToken): void
    {
        session([self::SESSION_KEY => $accessToken]);
    }

    public function get(): ?array
    {
        return session()->get(self::SESSION_KEY);
    }

    public function delete(): bool
    {
        session()->forget(self::SESSION_KEY);

        return true;
    }
}
