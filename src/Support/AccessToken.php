<?php

namespace TomShaw\Dropbox\Support;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

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
        $accessToken = $data['access_token'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new InvalidArgumentException('The token payload must contain a non-empty "access_token" string.');
        }

        $expiresIn = $data['expires_in'] ?? null;

        $expiresAt = match (true) {
            isset($data['expires_at']) => CarbonImmutable::make($data['expires_at']),
            is_numeric($expiresIn) => CarbonImmutable::now()->addSeconds((int) $expiresIn),
            default => null,
        };

        return new self(
            accessToken: $accessToken,
            refreshToken: self::optionalString($data['refresh_token'] ?? null),
            expiresAt: $expiresAt,
            tokenType: self::optionalString($data['token_type'] ?? null),
            uid: self::optionalString($data['uid'] ?? null),
            accountId: self::optionalString($data['account_id'] ?? null),
            scope: self::optionalString($data['scope'] ?? null),
        );
    }

    private static function optionalString(mixed $value): ?string
    {
        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            default => null,
        };
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
