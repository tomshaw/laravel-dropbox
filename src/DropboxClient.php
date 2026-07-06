<?php

namespace TomShaw\Dropbox;

use Carbon\CarbonImmutable;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\{ConnectionException, PendingRequest, RequestException, Response};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;
use TomShaw\Dropbox\Enums\Endpoints;
use TomShaw\Dropbox\Exceptions\{AuthenticationException, DropboxException, RateLimitException};
use TomShaw\Dropbox\Storage\StorageAdapterInterface;
use TomShaw\Dropbox\Support\{AccessToken, Arr};

class DropboxClient
{
    public const STATE_SESSION_KEY = 'dropbox:oauth_state';

    public const CODE_VERIFIER_SESSION_KEY = 'dropbox:code_verifier';

    protected StorageAdapterInterface $storageAdapter;

    public function __construct(?StorageAdapterInterface $storageAdapter = null)
    {
        $this->storageAdapter = $storageAdapter ?? $this->resolveConfiguredStorage();
    }

    public function setStorage(StorageAdapterInterface $storageAdapter): self
    {
        $this->storageAdapter = $storageAdapter;

        return $this;
    }

    public function getStorage(): StorageAdapterInterface
    {
        return $this->storageAdapter;
    }

    /**
     * @param  AccessToken|array<string, mixed>  $accessToken
     */
    public function setAccessToken(AccessToken|array $accessToken): self
    {
        if (is_array($accessToken)) {
            $accessToken = AccessToken::fromArray($accessToken);
        }

        $this->storageAdapter->set($accessToken->toArray());

        return $this;
    }

    public function getAccessToken(): ?AccessToken
    {
        $stored = $this->storageAdapter->get();

        if ($stored === null || ! isset($stored['access_token'])) {
            return null;
        }

        return AccessToken::fromArray($stored);
    }

    public function deleteAccessToken(): bool
    {
        return $this->storageAdapter->delete();
    }

    public function isEmpty(): bool
    {
        return ! filled(config('dropbox.accessToken')) && $this->getAccessToken() === null;
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Build the OAuth authorization URL. Issues a CSRF `state` value and a
     * PKCE code verifier, both kept in the session for the token exchange.
     */
    public function getAuthUrl(): string
    {
        $state = Str::random(40);
        $codeVerifier = Str::random(96);

        session([
            self::STATE_SESSION_KEY => $state,
            self::CODE_VERIFIER_SESSION_KEY => $codeVerifier,
        ]);

        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $query = array_filter([
            'response_type' => 'code',
            'client_id' => config('dropbox.clientId'),
            'redirect_uri' => config('dropbox.redirectUri'),
            'scope' => config('dropbox.scopes'),
            'token_access_type' => config('dropbox.accessType'),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], fn (mixed $value): bool => filled($value));

        return Endpoints::Authorize->url().'?'.http_build_query($query);
    }

    public function getAccessTokenWithAuthCode(string $code, ?string $state = null): AccessToken
    {
        $expectedState = session()->pull(self::STATE_SESSION_KEY);

        if ($expectedState !== null && (! is_string($expectedState) || ! hash_equals($expectedState, (string) $state))) {
            throw new AuthenticationException('Invalid OAuth state parameter.');
        }

        $response = $this->pending()->asForm()->post(Endpoints::Token->url(), array_filter([
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => config('dropbox.clientId'),
            'client_secret' => config('dropbox.clientSecret'),
            'redirect_uri' => config('dropbox.redirectUri'),
            'code_verifier' => session()->pull(self::CODE_VERIFIER_SESSION_KEY),
        ], fn (mixed $value): bool => filled($value)));

        $data = $this->decode($response);

        if (! isset($data['access_token'])) {
            throw new AuthenticationException('Dropbox token endpoint returned an unexpected response.');
        }

        return AccessToken::fromArray($data);
    }

    public function refreshAccessToken(): AccessToken
    {
        $token = $this->getAccessToken() ?? throw new AuthenticationException('No Dropbox access token to refresh.');

        if ($token->refreshToken === null) {
            throw new AuthenticationException('The stored Dropbox token has no refresh token.');
        }

        $response = $this->pending()->asForm()->post(Endpoints::Token->url(), [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refreshToken,
            'client_id' => config('dropbox.clientId'),
            'client_secret' => config('dropbox.clientSecret'),
        ]);

        $data = $this->decode($response);

        $accessToken = $data['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new AuthenticationException('Dropbox token endpoint returned an unexpected response.');
        }

        $expiresIn = $data['expires_in'] ?? null;

        $refreshed = $token->withRefreshed(
            accessToken: $accessToken,
            expiresAt: is_numeric($expiresIn) ? CarbonImmutable::now()->addSeconds((int) $expiresIn) : null,
        );

        $this->setAccessToken($refreshed);

        return $refreshed;
    }

    /**
     * Call an RPC endpoint on api.dropboxapi.com with bearer authentication.
     *
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>|null
     */
    public function rpc(string $path, ?array $body = null): ?array
    {
        $response = $this->pending()
            ->withToken($this->bearerToken())
            ->withBody(json_encode($body, JSON_THROW_ON_ERROR), 'application/json')
            ->post(Endpoints::Api->url($path));

        return $this->decode($response);
    }

    /**
     * Call an RPC endpoint using app (basic) authentication.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|null
     */
    public function appCheck(string $path, array $body): ?array
    {
        $response = $this->pending()
            ->withBasicAuth($this->configString('clientId'), $this->configString('clientSecret'))
            ->withBody(json_encode($body, JSON_THROW_ON_ERROR), 'application/json')
            ->post(Endpoints::Api->url($path));

        return $this->decode($response);
    }

    /**
     * Upload-style call to content.dropboxapi.com: arguments travel in the
     * Dropbox-API-Arg header and the body carries the raw payload.
     *
     * @param  array<string, mixed>  $arguments
     * @param  string|resource  $body
     * @return array<string, mixed>|null
     */
    public function contentUpload(string $path, array $arguments, mixed $body = ''): ?array
    {
        $response = $this->pending()
            ->withToken($this->bearerToken())
            ->withHeaders(['Dropbox-API-Arg' => json_encode($arguments, JSON_THROW_ON_ERROR)])
            ->withBody(Utils::streamFor($body), 'application/octet-stream')
            ->post(Endpoints::Content->url($path));

        return $this->decode($response);
    }

    /**
     * Download-style call to content.dropboxapi.com. When a sink path is
     * given the response body streams directly to that file.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function contentDownload(string $path, array $arguments, ?string $sink = null): Response
    {
        $request = $this->pending()
            ->withToken($this->bearerToken())
            ->withHeaders(['Dropbox-API-Arg' => json_encode($arguments, JSON_THROW_ON_ERROR)]);

        if ($sink !== null) {
            $request->withOptions(['sink' => $sink]);
        }

        $response = $request->post(Endpoints::Content->url($path));

        if ($response->failed()) {
            $this->handleFailure($response);
        }

        return $response;
    }

    /**
     * Resolve the bearer token, preferring a statically configured token and
     * transparently refreshing stored tokens that are about to expire.
     */
    protected function bearerToken(): string
    {
        $configToken = config('dropbox.accessToken');

        if (is_string($configToken) && $configToken !== '') {
            return $configToken;
        }

        $token = $this->getAccessToken() ?? throw new AuthenticationException('No Dropbox access token available. Complete the OAuth flow first.');

        if ($token->expiresSoon && $token->refreshToken !== null) {
            $token = $this->refreshAccessToken();
        }

        return $token->accessToken;
    }

    protected function pending(): PendingRequest
    {
        return Http::timeout($this->configInt('timeout', 30))
            ->retry(
                $this->configInt('retries', 3),
                fn (int $attempt, mixed $exception): int => $this->retryDelay($attempt, $exception),
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->status() === 429),
                throw: false,
            );
    }

    protected function retryDelay(int $attempt, mixed $exception): int
    {
        if ($exception instanceof RequestException) {
            $retryAfter = $exception->response->header('Retry-After');

            if ($retryAfter !== '') {
                return (int) $retryAfter * 1000;
            }
        }

        return $attempt * 1000;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decode(Response $response): ?array
    {
        if ($response->failed()) {
            $this->handleFailure($response);
        }

        if ($response->body() === '' || ! str_contains($response->header('Content-Type'), 'application/json')) {
            return null;
        }

        return Arr::stringKeyed($response->json());
    }

    protected function handleFailure(Response $response): never
    {
        throw match ($response->status()) {
            401 => AuthenticationException::fromResponse($response),
            429 => RateLimitException::fromResponse($response),
            default => DropboxException::fromResponse($response),
        };
    }

    protected function resolveConfiguredStorage(): StorageAdapterInterface
    {
        $storage = config('dropbox.storage');

        $adapter = is_string($storage) ? app($storage) : $storage;

        if (! $adapter instanceof StorageAdapterInterface) {
            throw new InvalidArgumentException('The dropbox.storage config value must reference a class implementing '.StorageAdapterInterface::class.'.');
        }

        return $adapter;
    }

    protected function configString(string $key): string
    {
        $value = config("dropbox.{$key}");

        return is_string($value) ? $value : '';
    }

    protected function configInt(string $key, int $default): int
    {
        $value = config("dropbox.{$key}");

        return is_numeric($value) ? (int) $value : $default;
    }
}
