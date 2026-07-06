<?php

namespace TomShaw\Dropbox\Resources;

class DropboxUsers extends DropboxResource
{
    /**
     * @return array<string, mixed>|null
     */
    public function getCurrentAccount(): ?array
    {
        return $this->client->rpc('users/get_current_account');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSpaceUsage(): ?array
    {
        return $this->client->rpc('users/get_space_usage');
    }
}
