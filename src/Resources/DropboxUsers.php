<?php

namespace TomShaw\Dropbox\Resources;

class DropboxUsers extends DropboxResource
{
    public function getCurrentAccount(): ?array
    {
        return $this->client->rpc('users/get_current_account');
    }

    public function getSpaceUsage(): ?array
    {
        return $this->client->rpc('users/get_space_usage');
    }
}
