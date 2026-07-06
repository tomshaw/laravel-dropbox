<?php

namespace TomShaw\Dropbox\Support;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final class AccessToken implements Arrayable
{
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?CarbonImmutable $expiresAt = null,
        public readonly ?string $tokenType = null,
        public readonly ?string $uid = null,
        public readonly ?string $accountId = null,
        public readonly ?string $scope = null,
    ) {}

    public bool $expiresSoon {
        get => $this->expiresAt !== null && $this->expiresAt->lte(CarbonImmutable::now()->addMinutes(5));
    }

    /**
     * Create a token from a stored payload or an OAuth token response. A
     * relative `expires_in` (seconds) is converted to an absolute `expires_at`.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $expiresAt = match (true) {
            isset($data['expires_at']) => CarbonImmutable::make($data['expires_at']),
            isset($data['expires_in']) => CarbonImmutable::now()->addSeconds((int) $data['expires_in']),
            default => null,
        };

        return new self(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'] ?? null,
            expiresAt: $expiresAt,
            tokenType: $data['token_type'] ?? null,
            uid: isset($data['uid']) ? (string) $data['uid'] : null,
            accountId: $data['account_id'] ?? null,
            scope: $data['scope'] ?? null,
        );
    }

    public function withRefreshed(string $accessToken, ?CarbonImmutable $expiresAt): self
    {
        return new self(
            accessToken: $accessToken,
            refreshToken: $this->refreshToken,
            expiresAt: $expiresAt,
            tokenType: $this->tokenType,
            uid: $this->uid,
            accountId: $this->accountId,
            scope: $this->scope,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt?->toIso8601String(),
            'token_type' => $this->tokenType,
            'uid' => $this->uid,
            'account_id' => $this->accountId,
            'scope' => $this->scope,
        ], fn (?string $value): bool => $value !== null);
    }
}
