<?php

return [
    'storage' => TomShaw\Dropbox\Storage\SessionTokenStorage::class,
    'clientId' => 'test',
    'clientSecret' => 'test',
    'redirectUri' => 'test',
    'accessToken' => '',
    'accessType' => 'offline',
    'scopes' => 'account_info.read files.metadata.write files.metadata.read files.content.write files.content.read sharing.write sharing.read',
];
