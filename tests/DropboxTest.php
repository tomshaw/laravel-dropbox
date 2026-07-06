<?php

use Illuminate\Support\Facades\Config;
use Mockery;
use TomShaw\Dropbox\DropboxClient;
use TomShaw\Dropbox\Storage\StorageAdapterInterface;
use TomShaw\Dropbox\Support\AccessToken;

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    $this->client = new DropboxClient;
});

it('sets the access token correctly', function () {
    $this->client->setAccessToken(['access_token' => 'test_token']);

    $result = $this->client->getAccessToken();

    expect($result)->toBeInstanceOf(AccessToken::class)
        ->and($result->accessToken)->toBe('test_token');
});

it('deletes the access token correctly', function () {
    $this->client->setAccessToken(['access_token' => 'test_token']);
    $this->client->deleteAccessToken();

    expect($this->client->getAccessToken())->toBeNull()
        ->and($this->client->isEmpty())->toBeTrue();
});

it('round-trips token attributes through storage', function () {
    $this->client->setAccessToken([
        'access_token' => 'test_token',
        'refresh_token' => 'refresh_token',
        'expires_in' => 14400,
        'token_type' => 'bearer',
        'uid' => '12345',
        'account_id' => 'dbid:abc123',
        'scope' => 'files.content.read',
    ]);

    $result = $this->client->getAccessToken();

    expect($result->refreshToken)->toBe('refresh_token')
        ->and($result->expiresAt)->not->toBeNull()
        ->and($result->expiresSoon)->toBeFalse()
        ->and($result->accountId)->toBe('dbid:abc123');
});

it('flags tokens close to expiry', function () {
    $this->client->setAccessToken([
        'access_token' => 'test_token',
        'expires_in' => 60,
    ]);

    expect($this->client->getAccessToken()->expiresSoon)->toBeTrue();
});

it('is not empty when a static access token is configured', function () {
    Config::set('dropbox.accessToken', 'static-token');

    expect($this->client->isEmpty())->toBeFalse();
});

it('sets and gets the storage adapter correctly', function () {
    $mockStorageAdapter = Mockery::mock(StorageAdapterInterface::class);

    $this->client->setStorage($mockStorageAdapter);

    expect($this->client->getStorage())->toBe($mockStorageAdapter);
});
