<?php

namespace Uneca\Chimera\Services;

use Illuminate\Support\Str;

class QueryFragmentFactory
{
    public static function make(string $connection)
    {
        $className = "App\Services\QueryFragments\\".Str::studly($connection).'QueryFragments';
        if (class_exists($className)) {
            return new $className;
        } else {
            return null;
        }
    }
}
