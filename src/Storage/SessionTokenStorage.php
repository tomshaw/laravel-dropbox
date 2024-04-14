<?php

namespace TomShaw\Dropbox\Storage;

class SessionTokenStorage implements StorageAdapterInterface
{
    public const SESSION_KEY = 'dropbox_token';

    public function set(array $accessToken): self
    {
        session([self::SESSION_KEY => $accessToken]);

        return $this;
    }

    public function get(): ?array
    {
        return session()->get(self::SESSION_KEY);
    }

    public function delete(): true
    {
        session()->forget(self::SESSION_KEY);

        return true;
    }
}
