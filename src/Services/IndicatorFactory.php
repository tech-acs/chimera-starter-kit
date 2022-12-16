<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Indicator;

class IndicatorFactory
{
    public static function make(Indicator $indicator)
    {
        $classPath = "App\Http\Livewire\\" . str_replace('/', '\\', $indicator->name);
        $instance = new $classPath;
        $instance->indicator = $indicator;
        $instance->graphDiv = $indicator->component;
        //$instance->getData();
        return $instance;

        /*$pieces = explode('.', $chart);
        $class = Str::studly(array_pop($pieces));
        $folders = array_map(function($str) { return Str::studly($str); }, $pieces);
        $classPath = "App\Http\Livewire\\" . implode('\\', $folders) . "\\$class";
        $instance = new $classPath;
        $instance->graphDiv = $chart;
        $instance->connection = $connection;
        return $instance;*/
    }
}
