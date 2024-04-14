<?php

namespace TomShaw\Dropbox\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $access_token
 * @property string|null $refresh_token
 * @property int $expires_in
 * @property string|null $token_type
 * @property string|null $uid
 * @property string|null $account_id
 * @property string|null $scope
 */
class DropboxToken extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
