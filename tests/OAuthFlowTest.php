<?php

use Illuminate\Support\Facades\{Config, Http};
use TomShaw\Dropbox\{Dropbox, DropboxClient};
use TomShaw\Dropbox\Exceptions\AuthenticationException;

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    $this->client = app(DropboxClient::class);
});

it('builds an authorization url with state and pkce challenge', function () {
    $url = $this->client->getAuthUrl();

    parse_str(parse_url($url, PHP_URL_QUERY), $query);

    expect($url)->toStartWith('https://www.dropbox.com/oauth2/authorize?')
        ->and($query['response_type'])->toBe('code')
        ->and($query['client_id'])->toBe('test')
        ->and($query['state'])->toBe(session(DropboxClient::STATE_SESSION_KEY))
        ->and($query['code_challenge_method'])->toBe('S256')
        ->and($query['code_challenge'])->not->toBeEmpty()
        ->and(session(DropboxClient::CODE_VERIFIER_SESSION_KEY))->not->toBeEmpty();
});

it('exchanges an authorization code with the pkce verifier', function () {
    Http::fake([
        'api.dropboxapi.com/oauth2/token' => Http::response([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 14400,
            'token_type' => 'bearer',
        ]),
    ]);

    $this->client->getAuthUrl();

    $state = session(DropboxClient::STATE_SESSION_KEY);
    $verifier = session(DropboxClient::CODE_VERIFIER_SESSION_KEY);

    $token = $this->client->getAccessTokenWithAuthCode('auth-code', $state);

    expect($token->accessToken)->toBe('new-access-token')
        ->and($token->refreshToken)->toBe('new-refresh-token');

    Http::assertSent(function ($request) use ($verifier) {
        return $request->url() === 'https://api.dropboxapi.com/oauth2/token'
            && $request['grant_type'] === 'authorization_code'
            && $request['code'] === 'auth-code'
            && $request['code_verifier'] === $verifier;
    });

    expect(session(DropboxClient::STATE_SESSION_KEY))->toBeNull()
        ->and(session(DropboxClient::CODE_VERIFIER_SESSION_KEY))->toBeNull();
});

it('rejects a mismatched oauth state', function () {
    $this->client->getAuthUrl();

    $this->client->getAccessTokenWithAuthCode('auth-code', 'tampered-state');
})->throws(AuthenticationException::class, 'Invalid OAuth state parameter.');

it('connects and stores the token through the manager', function () {
    Http::fake([
        'api.dropboxapi.com/oauth2/token' => Http::response([
            'access_token' => 'managed-token',
            'expires_in' => 14400,
        ]),
    ]);

    Dropbox::connect('auth-code');

    expect(app(DropboxClient::class)->getAccessToken()->accessToken)->toBe('managed-token');
});

it('refreshes an expiring token before authenticated requests', function () {
    Http::fake([
        'api.dropboxapi.com/oauth2/token' => Http::response([
            'access_token' => 'refreshed-token',
            'expires_in' => 14400,
        ]),
        'api.dropboxapi.com/2/*' => Http::response(['entries' => []]),
    ]);

    $this->client->setAccessToken([
        'access_token' => 'stale-token',
        'refresh_token' => 'refresh-token',
        'expires_in' => 10,
    ]);

    Dropbox::files()->listFolder();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.dropboxapi.com/oauth2/token'
        && $request['grant_type'] === 'refresh_token'
        && $request['refresh_token'] === 'refresh-token');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.dropboxapi.com/2/files/list_folder'
        && $request->header('Authorization')[0] === 'Bearer refreshed-token');

    expect($this->client->getAccessToken()->accessToken)->toBe('refreshed-token');
});

it('throws when refreshing without a stored token', function () {
    $this->client->refreshAccessToken();
})->throws(AuthenticationException::class);

it('revokes and deletes the stored token', function () {
    Http::fake([
        'api.dropboxapi.com/2/auth/token/revoke' => Http::response(null, 200),
    ]);

    $this->client->setAccessToken(['access_token' => 'test-token']);

    Dropbox::revoke();

    expect($this->client->getAccessToken())->toBeNull();
});
