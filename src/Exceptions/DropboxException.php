<?php

namespace TomShaw\Dropbox\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class DropboxException extends Exception
{
    /**
     * @param  array<string, mixed>|null  $errorBody
     */
    final public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly ?array $errorBody = null,
    ) {
        parent::__construct($message, $status);
    }

    public static function fromResponse(Response $response): static
    {
        $body = str_contains($response->header('Content-Type'), 'application/json')
            ? $response->json()
            : null;

        $summary = is_array($body) && isset($body['error_summary'])
            ? $body['error_summary']
            : $response->reason();

        return new static(
            message: "Dropbox API error ({$response->status()}): {$summary}",
            status: $response->status(),
            errorBody: is_array($body) ? $body : null,
        );
    }
}
