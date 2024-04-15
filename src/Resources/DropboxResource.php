<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\DropboxClient;

class DropboxResource
{
    public function __construct(
        protected DropboxClient $client
    ) {
    }
}
