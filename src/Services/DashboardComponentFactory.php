<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Scorecard;

class DashboardComponentFactory
{
    public static function makeIndicator(Indicator $indicator)
    {
        $classPath = "App\Http\Livewire\\" . str_replace('/', '\\', $indicator->name);
        try {
            $instance = new $classPath;
            $instance->indicator = $indicator;
            $instance->graphDiv = $indicator->component;
            return $instance;
        } catch (\Exception $exception) {
            logger("Exception in DashboardComponentFactory", ['exception' => $exception->getMessage()]);
            return null;
        }
    }

    public static function makeScorecard(Scorecard $scorecard)
    {
        $classPath = "App\Http\Livewire\\Scorecard\\" . str_replace('/', '\\', $scorecard->name);
        try {
            $instance = new $classPath;
            $instance->indicator = $scorecard;
            return $instance;
        } catch (\Exception $exception) {
            logger("Exception in DashboardComponentFactory", ['exception' => $exception->getMessage()]);
            return null;
        }
    }

    public static function makeMapIndicator(MapIndicator $mapIndicator)
    {
        $classPath = $mapIndicator->fully_qualified_classname;
        try {
            $instance = new $classPath;
            return $instance;
        } catch (\Exception $exception) {
            logger("Exception in DashboardComponentFactory", ['exception' => $exception->getMessage()]);
            return null;
        }
    }
}
