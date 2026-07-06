<?php

use TomShaw\Dropbox\Storage\DatabaseTokenStorage;

/**
 * Configuration options for Dropbox.
 *
 * @return array<string, mixed> Configuration options.
 */
return [
    /**
     * The class responsible for storing Dropbox tokens.
     *
     * @var string
     */
    'storage' => DatabaseTokenStorage::class,

    /**
     * The client ID for the Dropbox application.
     *
     * @var string|null
     */
    'clientId' => env('DROPBOX_CLIENT_ID'),

    /**
     * The client secret for the Dropbox application.
     *
     * @var string|null
     */
    'clientSecret' => env('DROPBOX_CLIENT_SECRET'),

    /**
     * The URI to redirect to after Dropbox authentication.
     *
     * @var string|null
     */
    'redirectUri' => env('DROPBOX_REDIRECT_URI'),

    /**
     * A static access token used for all API requests. When set, the OAuth
     * flow and token storage are bypassed entirely. Useful for single-account
     * server-side integrations using a scoped app token.
     *
     * @var string|null
     */
    'accessToken' => env('DROPBOX_ACCESS_TOKEN'),

    /**
     * The access type for the Dropbox application.
     *
     * @var string|null
     */
    'accessType' => env('DROPBOX_ACCESS_TYPE'),

    /**
     * The scopes for the Dropbox application. If omitted will request all scopes selected on Permissions tab.
     *
     * @var string|null
     */
    'scopes' => env('DROPBOX_ACCESS_SCOPES'),

    /**
     * The request timeout in seconds for Dropbox API calls.
     *
     * @var int
     */
    'timeout' => env('DROPBOX_TIMEOUT', 30),

    /**
     * The number of attempts for rate-limited or failed connections.
     *
     * @var int
     */
    'retries' => env('DROPBOX_RETRIES', 3),
];
