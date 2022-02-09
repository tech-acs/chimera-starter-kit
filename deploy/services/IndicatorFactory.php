<?php

namespace App\Services;

use Illuminate\Support\Str;

class IndicatorFactory
{
    public static function make(string $connection, string $chart)
    {
        $pieces = explode('.', $chart);
        $class = Str::studly(array_pop($pieces));
        $folders = array_map(function($str) { return Str::studly($str); }, $pieces);
        $classPath = "App\Http\Livewire\\" . implode('\\', $folders) . "\\$class";
        $instance = new $classPath;
        $instance->graphDiv = $chart;
        $instance->connection = $connection;
        return $instance;
    }
}
