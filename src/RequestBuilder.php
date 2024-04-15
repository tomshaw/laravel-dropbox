<?php

namespace TomShaw\Dropbox;

use GuzzleHttp\Client;

class RequestBuilder
{
    private string $method;

    private string $url;

    private mixed $body = null;

    private array $params = [];

    private array $headers = [];

    public function __construct(
        protected Client $client
    ) {
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setBody(mixed $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function reset(): self
    {
        $this->method = '';
        $this->url = '';
        $this->body = null;
        $this->params = [];
        $this->headers = [];

        return $this;
    }

    public function send(): ?array
    {
        $method = $this->getMethod();

        $url = $this->getUrl();

        $body = $this->getBody();

        $params = $this->getParams();

        $headers = $this->getHeaders();

        $options = [
            'headers' => $headers,
        ];

        if (is_resource($body)) {
            $options['body'] = $body;
        } else {
            $options['body'] = count($body) > 0 ? json_encode($body) : json_encode(null);
        }

        if (count($params)) {
            $options['form_params'] = $params;
        }

        $this->reset();

        $response = $this->client->request($method, $url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
