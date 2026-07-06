<?php

namespace TomShaw\Dropbox\Enums;

enum Endpoints: string
{
    case Authorize = 'https://www.dropbox.com/oauth2/authorize';
    case Api = 'https://api.dropboxapi.com/2/';
    case Content = 'https://content.dropboxapi.com/2/';
    case Token = 'https://api.dropboxapi.com/oauth2/token';

    public function url(string $path = ''): string
    {
        return $this->value.ltrim($path, '/');
    }
}
