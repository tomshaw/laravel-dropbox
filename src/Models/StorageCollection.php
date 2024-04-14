<?php

namespace TomShaw\Dropbox\Models;

use Illuminate\Support\Collection;

class StorageCollection extends Collection
{
    public $id;
    public $user_id;
    public $access_token;
    public $refresh_token;
    public $expires_in;
    public $token_type;
    public $uid;
    public $account_id;
    public $scope;
}
