<?php

namespace TomShaw\Dropbox\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TomShaw\Dropbox\DropboxClient;

class DropboxConnect
{
    public function __construct(
        protected readonly DropboxClient $client
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->client->isEmpty()) {
            if ($request->expectsJson()) {
                abort(401, 'Dropbox authentication required.');
            }

            return redirect()->away($this->client->getAuthUrl());
        }

        return $next($request);
    }
}
