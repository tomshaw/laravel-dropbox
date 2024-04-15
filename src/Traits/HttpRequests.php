<?php

namespace TomShaw\Dropbox\Traits;

trait HttpRequests
{
    public function getContentType(string $contentType = 'application/json'): array
    {
        return [
            'Content-Type' => $contentType,
        ];
    }

    public function getAuthorizationHeader(bool $authTypeBasic = false): array
    {
        if ($authTypeBasic) {
            return [
                'Authorization' => 'Basic '.base64_encode(config('dropbox.clientId').':'.config('dropbox.clientSecret')),
            ];
        }

        return [
            'Authorization' => 'Bearer '.$this->getAccessToken()->get('access_token'),
        ];
    }

    public function post(string $endpoint, mixed $body = null, array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('POST', endpoint: $endpoint, body: $body, params: $params, headers: $headers);
    }

    public function get(string $endpoint, mixed $body = null, array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('GET', endpoint: $endpoint, body: $body, params: $params, headers: $headers);
    }

    public function put(string $endpoint, mixed $body = null, array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('PUT', endpoint: $endpoint, body: $body, params: $params, headers: $headers);
    }

    public function delete(string $endpoint, mixed $body = null, array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('DELETE', endpoint: $endpoint, body: $body, params: $params, headers: $headers);
    }

    public function sendRequest(string $method, string $endpoint, mixed $body = null, array $params = [], array $headers = []): ?array
    {
        $this->builder->setMethod($method)->setUrl($endpoint);

        if ($body) {
            $this->builder->setBody($body);
        }

        if (count($params)) {
            $this->builder->setParams($params);
        }

        if (count($headers)) {
            $this->builder->setHeaders($headers);
        }

        return $this->builder->send();
    }
}
