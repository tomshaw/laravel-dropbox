<?php

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use TomShaw\Dropbox\DropboxClient;
use TomShaw\Dropbox\Storage\StorageAdapterInterface;

test('instance check', function () {
    $this->assertTrue($this instanceof \PHPUnit\Framework\TestCase);
});

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    $this->client = new DropboxClient(new Client());
});

it('sets the access token correctly', function () {
    $this->client->setAccessToken(['access_token' => 'test_token']);

    $result = $this->client->getAccessToken()->toArray();

    expect($result)->toBeArray()->and($result['access_token'])->toBe('test_token');
});

it('deletes the access token correctly', function () {
    $this->client->setAccessToken(['access_token' => 'test_token']);
    $this->client->deleteAccessToken();

    $result = $this->client->getAccessToken();

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->isEmpty())->toBeTrue();
});

it('gets the access token correctly', function () {
    $this->client->setAccessToken(['access_token' => 'test_token']);

    $result = $this->client->getAccessToken()->toArray();

    expect($result)->toBeArray()->and($result['access_token'])->toBe('test_token');
});

it('sets and gets the storage adapter correctly', function () {
    $mockStorageAdapter = \Mockery::mock(StorageAdapterInterface::class);

    $this->client->setStorage($mockStorageAdapter);

    $result = $this->client->getStorage();

    expect($result)->toBe($mockStorageAdapter);
});
