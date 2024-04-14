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

    public function post(string $endpoint, array $body = [], array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('POST', $endpoint, $body, $params, $headers);
    }

    public function get(string $endpoint, array $body = [], array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('GET', $endpoint, $body, $params, $headers);
    }

    public function put(string $endpoint, array $body = [], array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('PUT', $endpoint, $body, $params, $headers);
    }

    public function delete(string $endpoint, array $body = [], array $params = [], array $headers = []): ?array
    {
        return $this->sendRequest('DELETE', $endpoint, $body, $params, $headers);
    }

    public function sendRequest(string $method, string $endpoint, array $body = [], array $params = [], array $headers = []): ?array
    {
        $this->builder->setMethod($method)->setUrl($endpoint);

        if (count($body)) {
            $this->builder->setBody($body);
        }

        if (count($params)) {
            $this->builder->setParams($params);
        }

        if (count($headers)) {
            foreach ($headers as $key => $value) {
                $this->builder->setHeader($key, $value);
            }
        }

        return $this->builder->send();
    }
}
