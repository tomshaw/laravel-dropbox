<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Config, DB, Schema};
use TomShaw\Dropbox\DropboxClient;
use TomShaw\Dropbox\Exceptions\DropboxException;
use TomShaw\Dropbox\Models\DropboxToken;
use TomShaw\Dropbox\Storage\DatabaseTokenStorage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));
    Config::set('dropbox.storage', DatabaseTokenStorage::class);

    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }
});

function actingUser(): AuthUser
{
    $user = new class extends AuthUser
    {
        protected $table = 'users';

        protected $guarded = [];
    };

    return $user->newQuery()->create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'secret',
    ]);
}

it('refuses to store a token without an authenticated user', function () {
    (new DatabaseTokenStorage)->set(['access_token' => 'test-token']);
})->throws(DropboxException::class);

it('returns null when no user is authenticated', function () {
    expect((new DatabaseTokenStorage)->get())->toBeNull();
});

it('stores tokens encrypted at rest', function () {
    $this->actingAs(actingUser());

    app(DropboxClient::class)->setAccessToken([
        'access_token' => 'plain-access-token',
        'refresh_token' => 'plain-refresh-token',
        'expires_in' => 14400,
    ]);

    $raw = DB::table('dropbox_tokens')->first();

    expect($raw->access_token)->not->toBe('plain-access-token')
        ->and($raw->refresh_token)->not->toBe('plain-refresh-token');

    $token = app(DropboxClient::class)->getAccessToken();

    expect($token->accessToken)->toBe('plain-access-token')
        ->and($token->refreshToken)->toBe('plain-refresh-token')
        ->and($token->expiresAt)->not->toBeNull();
});

it('keeps a single token row per user', function () {
    $this->actingAs(actingUser());

    $client = app(DropboxClient::class);

    $client->setAccessToken(['access_token' => 'first-token']);
    $client->setAccessToken(['access_token' => 'second-token']);

    expect(DropboxToken::query()->count())->toBe(1)
        ->and($client->getAccessToken()->accessToken)->toBe('second-token');
});

it('deletes only the authenticated users token', function () {
    $this->actingAs(actingUser());

    $client = app(DropboxClient::class);

    $client->setAccessToken(['access_token' => 'test-token']);

    expect($client->deleteAccessToken())->toBeTrue()
        ->and($client->getAccessToken())->toBeNull();
});
