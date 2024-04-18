<?php

namespace TomShaw\Dropbox\Traits;

trait HttpRequests
{
    protected array $headers = [];

    public function setContentType(string $contentType = 'application/json'): self
    {
        $this->headers['Content-Type'] = $contentType;

        return $this;
    }

    public function addAuthorizationBasic(): self
    {
        $this->headers['Authorization'] = 'Basic '.base64_encode(config('dropbox.clientId').':'.config('dropbox.clientSecret'));

        return $this;
    }

    public function addAuthorizationBearer(): self
    {
        $this->headers['Authorization'] = 'Bearer '.$this->getAccessToken()->get('access_token');

        return $this;
    }

    public function addApiArguments(array $arguments): self
    {
        $this->headers['Dropbox-API-Arg'] = json_encode($arguments);

        return $this;
    }

    public function headers(bool $bearer = false, bool $basic = false, ?string $contentType = 'application/json', array $arguments = []): self
    {
        if ($bearer) {
            $this->addAuthorizationBearer();
        }

        if ($basic) {
            $this->addAuthorizationBasic();
        }

        if ($contentType) {
            $this->setContentType($contentType);
        }

        if (count($arguments)) {
            $this->addApiArguments($arguments);
        }

        return $this;
    }

    public function post(string $endpoint, mixed $body = null, array $params = []): mixed
    {
        return $this->sendRequest('POST', endpoint: $endpoint, body: $body, params: $params);
    }

    public function get(string $endpoint, mixed $body = null, array $params = []): mixed
    {
        return $this->sendRequest('GET', endpoint: $endpoint, body: $body, params: $params);
    }

    public function put(string $endpoint, mixed $body = null, array $params = []): mixed
    {
        return $this->sendRequest('PUT', endpoint: $endpoint, body: $body, params: $params);
    }

    public function delete(string $endpoint, mixed $body = null, array $params = []): mixed
    {
        return $this->sendRequest('DELETE', endpoint: $endpoint, body: $body, params: $params);
    }

    public function sendRequest(string $method, string $endpoint, mixed $body = null, array $params = []): mixed
    {
        $options = [
            'headers' => $this->headers,
        ];

        if (count($params)) {
            $options['form_params'] = $params;
        }

        if (is_resource($body)) {
            $options['body'] = $body;
        } elseif (! in_array('Dropbox-API-Arg', array_keys($this->headers))) {
            $options['body'] = count($body) > 0 ? json_encode($body) : json_encode(null);
        }

        $response = $this->client->request($method, $endpoint, $options);

        $contents = $response->getBody()->getContents();

        $this->headers = [];

        if ($response->getHeaderLine('Content-Type') === 'application/json') {
            return json_decode($contents, true);
        }

        return $contents;
    }
}
