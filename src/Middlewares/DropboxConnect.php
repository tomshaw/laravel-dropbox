<?php

namespace TomShaw\Dropbox\Middlewares;

use TomShaw\Dropbox\DropboxClient;

class DropboxConnect
{
    protected $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    public function handle($request, \Closure $next)
    {
        if ($this->client->isEmpty()) {
            return redirect()->away($this->client->getAuthUrl());
        }

        $this->client->refreshAccessToken();

        return $next($request);
    }
}
