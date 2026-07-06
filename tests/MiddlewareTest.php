<?php

use Illuminate\Support\Facades\{Config, Route};
use TomShaw\Dropbox\DropboxClient;

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    Route::middleware('dropbox')->get('/dropbox-protected', fn () => 'ok');
});

it('redirects browser requests without a token to dropbox authorization', function () {
    $response = $this->get('/dropbox-protected');

    $response->assertRedirect();

    expect($response->headers->get('Location'))->toStartWith('https://www.dropbox.com/oauth2/authorize?');
});

it('returns 401 for json requests without a token', function () {
    $this->getJson('/dropbox-protected')->assertUnauthorized();
});

it('passes requests through when a token is stored', function () {
    app(DropboxClient::class)->setAccessToken(['access_token' => 'test-token']);

    $this->get('/dropbox-protected')->assertOk()->assertSee('ok');
});

it('passes requests through when a static access token is configured', function () {
    Config::set('dropbox.accessToken', 'static-token');

    $this->get('/dropbox-protected')->assertOk();
});
