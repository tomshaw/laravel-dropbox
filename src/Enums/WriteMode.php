<?php

namespace TomShaw\Dropbox\Enums;

enum WriteMode: string
{
    case Add = 'add';
    case Overwrite = 'overwrite';
}
