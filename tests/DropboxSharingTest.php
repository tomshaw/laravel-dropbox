<?php

use Illuminate\Support\Facades\{Config, Http};
use TomShaw\Dropbox\{Dropbox, DropboxClient};

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    app(DropboxClient::class)->setAccessToken(['access_token' => 'test-token']);
});

it('creates a shared link with the given settings', function () {
    Http::fake(['api.dropboxapi.com/2/sharing/create_shared_link_with_settings' => Http::response(['url' => 'https://www.dropbox.com/s/abc123'])]);

    $result = Dropbox::sharing()->createSharedLinkWithSettings('/report.pdf', ['requested_visibility' => 'public']);

    expect($result)->toBe(['url' => 'https://www.dropbox.com/s/abc123']);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings'
        && $request->header('Authorization')[0] === 'Bearer test-token'
        && json_decode($request->body(), true) === [
            'path' => '/report.pdf',
            'settings' => ['requested_visibility' => 'public'],
        ]);
});

it('omits the settings key when no settings are given', function () {
    Http::fake(['api.dropboxapi.com/2/sharing/create_shared_link_with_settings' => Http::response(['url' => 'https://www.dropbox.com/s/abc123'])]);

    Dropbox::sharing()->createSharedLinkWithSettings('/report.pdf');

    Http::assertSent(fn ($request) => json_decode($request->body(), true) === ['path' => '/report.pdf']);
});

it('lists shared links omitting null options', function () {
    Http::fake(['api.dropboxapi.com/2/sharing/list_shared_links' => Http::response(['links' => []])]);

    $result = Dropbox::sharing()->listSharedLinks('/report.pdf');

    expect($result)->toBe(['links' => []]);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.dropboxapi.com/2/sharing/list_shared_links'
        && json_decode($request->body(), true) === [
            'path' => '/report.pdf',
            'direct_only' => false,
        ]);
});

it('lists shared links by cursor', function () {
    Http::fake(['api.dropboxapi.com/2/sharing/list_shared_links' => Http::response(['links' => []])]);

    Dropbox::sharing()->listSharedLinks(cursor: 'cursor-1', directOnly: true);

    Http::assertSent(fn ($request) => json_decode($request->body(), true) === [
        'cursor' => 'cursor-1',
        'direct_only' => true,
    ]);
});

it('revokes a shared link', function () {
    Http::fake(['api.dropboxapi.com/2/sharing/revoke_shared_link' => Http::response(null, 200)]);

    Dropbox::sharing()->revokeSharedLink('https://www.dropbox.com/s/abc123');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.dropboxapi.com/2/sharing/revoke_shared_link'
        && json_decode($request->body(), true) === ['url' => 'https://www.dropbox.com/s/abc123']);
});
