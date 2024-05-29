<?php

namespace Uneca\Chimera\Services;

use Livewire\Mechanisms\ComponentRegistry;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Models\Report;

class DashboardComponentFactory
{
    public static function makeIndicator(Indicator $indicator)
    {
        $classPath = "App\\Livewire\\" . str_replace('/', '\\', $indicator->name);
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
        $classPath = "App\\Livewire\\Scorecard\\" . str_replace('/', '\\', $scorecard->name);
        try {
            $instance = new $classPath;
            $instance->scorecard = $scorecard;
            return $instance;
        } catch (\Exception $exception) {
            logger("Exception in DashboardComponentFactory", ['exception' => $exception->getMessage()]);
            return null;
        }
    }

    public static function makeCaseStats(DataSource $dataSource)
    {
        $classPath = app(ComponentRegistry::class)->getClass($dataSource->case_stats_component);
        try {
            $instance = new $classPath;
            $instance->dataSource = $dataSource;
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

    public static function makeReport(Report $report)
    {
        $classPath = "App\\Reports\\" . str($report->name)->replace('/', '\\');
        try {
            $instance = new $classPath($report);
            return $instance;
        } catch (\Exception $exception) {
            logger("Exception in DashboardComponentFactory", ['exception' => $exception->getMessage()]);
            return null;
        }
    }
}
