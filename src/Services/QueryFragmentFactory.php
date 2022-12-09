<?php

namespace Uneca\Chimera\Services;

use Exception;
use Illuminate\Support\Str;

class QueryFragmentFactory
{
    public static function make(string $connection)
    {
        $className = "App\Services\QueryFragments\\" . Str::studly($connection) . "QueryFragments";
        try {
            return new $className;
        } catch (Exception $exception) {
            return null;
        }
    }
}
