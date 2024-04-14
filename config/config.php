<?php
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
    'storage' => TomShaw\Dropbox\Storage\DatabaseTokenStorage::class,

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
     * The access token for Dropbox API requests.
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
];
