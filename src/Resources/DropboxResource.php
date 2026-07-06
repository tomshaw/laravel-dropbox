<?php

namespace TomShaw\Dropbox\Resources;

use TomShaw\Dropbox\DropboxClient;

abstract class DropboxResource
{
    public function __construct(
        protected readonly DropboxClient $client
    ) {}
}
