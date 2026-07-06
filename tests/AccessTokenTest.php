<?php

use Carbon\CarbonImmutable;
use TomShaw\Dropbox\Support\AccessToken;

it('throws when the payload has no access token', function () {
    AccessToken::fromArray([]);
})->throws(InvalidArgumentException::class);

it('throws when the access token is not a string', function () {
    AccessToken::fromArray(['access_token' => 12345]);
})->throws(InvalidArgumentException::class);

it('throws when the access token is an empty string', function () {
    AccessToken::fromArray(['access_token' => '']);
})->throws(InvalidArgumentException::class);

it('coerces a numeric uid to a string', function () {
    $token = AccessToken::fromArray(['access_token' => 'token', 'uid' => 12345]);

    expect($token->uid)->toBe('12345');
});

it('drops optional fields with unusable types', function () {
    $token = AccessToken::fromArray([
        'access_token' => 'token',
        'refresh_token' => ['unexpected'],
        'token_type' => null,
        'scope' => false,
    ]);

    expect($token->refreshToken)->toBeNull()
        ->and($token->tokenType)->toBeNull()
        ->and($token->scope)->toBeNull();
});

it('prefers an absolute expires_at over a relative expires_in', function () {
    $token = AccessToken::fromArray([
        'access_token' => 'token',
        'expires_at' => '2030-01-01T00:00:00+00:00',
        'expires_in' => 60,
    ]);

    expect($token->expiresAt->toIso8601String())->toBe('2030-01-01T00:00:00+00:00');
});

it('ignores a non-numeric expires_in', function () {
    $token = AccessToken::fromArray([
        'access_token' => 'token',
        'expires_in' => 'soon',
    ]);

    expect($token->expiresAt)->toBeNull();
});

it('omits null attributes when converting to an array', function () {
    $token = new AccessToken(accessToken: 'token', expiresAt: CarbonImmutable::parse('2030-01-01T00:00:00+00:00'));

    expect($token->toArray())->toBe([
        'access_token' => 'token',
        'expires_at' => '2030-01-01T00:00:00+00:00',
    ]);
});
