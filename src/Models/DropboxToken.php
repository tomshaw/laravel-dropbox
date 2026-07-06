<?php

namespace TomShaw\Dropbox\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $access_token
 * @property string|null $refresh_token
 * @property CarbonImmutable|null $expires_at
 * @property string|null $token_type
 * @property string|null $uid
 * @property string|null $account_id
 * @property string|null $scope
 */
class DropboxToken extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
