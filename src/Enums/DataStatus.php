<?php

namespace Uneca\Chimera\Enums;

enum DataStatus: string
{
    case PENDING = 'pending';
    case RENDERABLE = 'renderable';
    case EMPTY = 'empty';
    case INAPPLICABLE = 'inapplicable';
}
