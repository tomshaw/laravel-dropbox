<?php

namespace TomShaw\Dropbox\Exceptions;

use Illuminate\Http\Client\Response;

class RateLimitException extends DropboxException
{
    public ?int $retryAfter = null;

    public static function fromResponse(Response $response): static
    {
        $exception = parent::fromResponse($response);

        $retryAfter = $response->header('Retry-After');

        $exception->retryAfter = $retryAfter !== '' ? (int) $retryAfter : null;

        return $exception;
    }
}
