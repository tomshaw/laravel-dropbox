<?php

namespace TomShaw\Dropbox;

use GuzzleHttp\Client;

class RequestBuilder
{
    private string $method;

    private string $url;

    private array $headers = [];

    private array $body = [];

    private array $params = [];

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

    public function setBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): array
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

    public function reset(): self
    {
        $this->method = '';
        $this->url = '';
        $this->headers = [];
        $this->body = [];
        $this->params = [];

        return $this;
    }

    public function send(): ?array
    {
        $method = $this->getMethod();

        $url = $this->getUrl();

        $headers = $this->getHeaders();

        $body = $this->getBody();

        $params = $this->getParams();

        $options = [
            'headers' => $headers,
        ];

        $options['body'] = count($body) > 0 ? json_encode($body) : json_encode(null);

        if (count($params)) {
            $options['form_params'] = $params;
        }

        $this->reset();

        $response = $this->client->request($method, $url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
