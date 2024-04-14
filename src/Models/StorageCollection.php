<?php

namespace TomShaw\Dropbox\Models;

use Illuminate\Support\Collection;

class StorageCollection extends Collection
{
    public $access_token;

    public $expires_in;
}
