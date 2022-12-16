<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Indicator;

class IndicatorFactory
{
    public static function make(Indicator $indicator)
    {
        $classPath = "App\Http\Livewire\\" . str_replace('/', '\\', $indicator->name);
        try {
            $instance = new $classPath;
            $instance->indicator = $indicator;
            $instance->graphDiv = $indicator->component;
            return $instance;
        } catch (Exception $exception) {
            return null;
        }
    }
}
