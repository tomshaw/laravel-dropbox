<?php

namespace TomShaw\Dropbox\Enums;

enum Endpoints: string
{
    case Authorize = 'https://www.dropbox.com/oauth2/authorize';
    case Base = 'https://api.dropboxapi.com/2/';
    case Content = 'https://content.dropboxapi.com/2/';
    case Token = 'https://api.dropbox.com/oauth2/token';
}
