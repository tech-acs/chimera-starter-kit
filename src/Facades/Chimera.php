<?php

namespace Uneca\Chimera\Facades;

use Illuminate\Support\Facades\Facade;

class Chimera extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chimera';
    }
}
