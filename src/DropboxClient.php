<?php

namespace TomShaw\Dropbox;

use GuzzleHttp\Client;
use TomShaw\Dropbox\Enums\Endpoints;
use TomShaw\Dropbox\Models\StorageCollection;
use TomShaw\Dropbox\Storage\StorageAdapterInterface;
use TomShaw\Dropbox\Traits\HttpRequests;

class DropboxClient
{
    use HttpRequests;

    protected RequestBuilder $builder;

    protected StorageAdapterInterface $storageAdapter;

    public function __construct(
        protected client $client,
    ) {
        $this->builder = new RequestBuilder($client);

        $this->setStorage(app(config('dropbox.storage')));
    }

    public function setStorage(StorageAdapterInterface $storageAdapter): self
    {
        if (! $storageAdapter instanceof StorageAdapterInterface) {
            throw new \Exception('Invalid token storage.');
        }

        $this->storageAdapter = $storageAdapter;

        return $this;
    }

    public function getStorage(): StorageAdapterInterface
    {
        return $this->storageAdapter;
    }

    public function setAccessToken(array $accessToken): self
    {
        $this->getStorage()->set($accessToken);

        return $this;
    }

    public function getAccessToken(): StorageCollection
    {
        return new StorageCollection($this->getStorage()->get());
    }

    public function deleteAccessToken(): bool|int
    {
        return $this->getStorage()->delete();
    }

    public function isEmpty(): bool
    {
        return $this->getAccessToken()->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->getAccessToken()->isNotEmpty();
    }

    public function getAuthUrl()
    {
        return Endpoints::Authorize->value.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => config('dropbox.clientId'),
            'redirect_uri' => config('dropbox.redirectUri'),
            'scope' => config('dropbox.scopes'),
            'token_access_type' => config('dropbox.accessType'),
        ]);
    }

    public function getAccessTokenWithAuthCode(string $code): array
    {
        $response = $this->post(Endpoints::Token->value, params: [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => config('dropbox.clientId'),
            'client_secret' => config('dropbox.clientSecret'),
            'redirect_uri' => config('dropbox.redirectUri'),
        ]);

        $response['expires_in'] = time() + $response['expires_in'];

        return $response;
    }

    public function refreshAccessToken(): ?StorageCollection
    {
        $token = $this->getAccessToken();

        if ($token->isEmpty()) {
            return null;
        }

        $now = time() + 300;
        if ($token->get('expires_in') <= $now) {

            $response = $this->post(Endpoints::Token->value, params: [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token->get('refresh_token'),
                'client_id' => config('dropbox.clientId'),
                'client_secret' => config('dropbox.clientSecret'),
            ]);

            $token->put('access_token', $response['access_token']);
            $token->put('expires_in', time() + $response['expires_in']);

            $this->setAccessToken($token->toArray());
        }

        return $token;
    }
}
