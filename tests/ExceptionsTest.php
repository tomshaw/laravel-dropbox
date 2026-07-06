<?php

use Illuminate\Support\Facades\{Config, Http};
use TomShaw\Dropbox\{Dropbox, DropboxClient};
use TomShaw\Dropbox\Exceptions\{AuthenticationException, DropboxException, RateLimitException};

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    app(DropboxClient::class)->setAccessToken(['access_token' => 'test-token']);
});

it('throws an authentication exception for 401 responses', function () {
    Http::fake(['api.dropboxapi.com/*' => Http::response(['error_summary' => 'invalid_access_token/'], 401)]);

    Dropbox::users()->getCurrentAccount();
})->throws(AuthenticationException::class, 'invalid_access_token/');

it('throws a rate limit exception with retry-after once retries are exhausted', function () {
    Http::fake(['api.dropboxapi.com/*' => Http::response(['error_summary' => 'too_many_requests/'], 429, ['Retry-After' => '0'])]);

    try {
        Dropbox::users()->getCurrentAccount();

        $this->fail('Expected a RateLimitException to be thrown.');
    } catch (RateLimitException $exception) {
        expect($exception->status)->toBe(429)
            ->and($exception->retryAfter)->toBe(0);
    }
});

it('retries rate limited requests and succeeds', function () {
    Http::fake([
        'api.dropboxapi.com/*' => Http::sequence()
            ->push(['error_summary' => 'too_many_requests/'], 429, ['Retry-After' => '0'])
            ->push(['name' => 'Tom']),
    ]);

    expect(Dropbox::users()->getCurrentAccount())->toBe(['name' => 'Tom']);

    Http::assertSentCount(2);
});

it('throws a dropbox exception with the error summary for api errors', function () {
    Http::fake(['api.dropboxapi.com/*' => Http::response(['error_summary' => 'path/not_found/'], 409)]);

    try {
        Dropbox::files()->getMetadata('/missing.txt');

        $this->fail('Expected a DropboxException to be thrown.');
    } catch (DropboxException $exception) {
        expect($exception->getMessage())->toContain('path/not_found/')
            ->and($exception->status)->toBe(409)
            ->and($exception->errorBody)->toBe(['error_summary' => 'path/not_found/']);
    }
});

it('throws when calling authenticated endpoints without any token', function () {
    app(DropboxClient::class)->deleteAccessToken();

    Dropbox::users()->getCurrentAccount();
})->throws(AuthenticationException::class);

it('uses the configured static access token when present', function () {
    Config::set('dropbox.accessToken', 'static-token');

    Http::fake(['api.dropboxapi.com/*' => Http::response(['name' => 'Tom'])]);

    app(DropboxClient::class)->deleteAccessToken();

    Dropbox::users()->getCurrentAccount();

    Http::assertSent(fn ($request) => $request->header('Authorization')[0] === 'Bearer static-token');
});

it('verifies app credentials with basic authentication', function () {
    Http::fake(['api.dropboxapi.com/2/check/app' => Http::response(['result' => 'ping'])]);

    Dropbox::check()->app();

    Http::assertSent(fn ($request) => str_starts_with($request->header('Authorization')[0], 'Basic ')
        && json_decode($request->body(), true) === ['query' => 'ping']);
});

it('verifies the user token with bearer authentication', function () {
    Http::fake(['api.dropboxapi.com/2/check/user' => Http::response(['result' => 'ping'])]);

    Dropbox::check()->user();

    Http::assertSent(fn ($request) => $request->header('Authorization')[0] === 'Bearer test-token');
});
