<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\IndicatorCaching;
use Uneca\Chimera\Services\MapIndicatorCaching;
use Uneca\Chimera\Services\ScorecardCaching;

class CacheData extends Command
{
    protected $signature = 'chimera:cache {--max-level=0} {--questionnaire=}';

    protected $description = "Calculate and cache (published) data";

    public function __construct()
    {
        parent::__construct();
    }

    private function cacheIndicators()
    {
        if ($this->option('questionnaire')) {
            $indicatorsToCache = Indicator::ofQuestionnaire($this->option('questionnaire'))->published()->get();
        } else {
            $indicatorsToCache = Indicator::published()->get();
        }

        $maxLevel = $this->option('max-level') ?? 0;
        if ($maxLevel > 0 && ($maxLevel >= AreaHierarchy::count())) {
            $maxLevel = AreaHierarchy::count() - 1;
        }
        foreach ($indicatorsToCache as $indicator) {
            $this->newLine()->info($indicator->name);
            $startTime = time();
            (new IndicatorCaching($indicator, []))->update(); // National level - no filters (non-level)
            for ($level = 0; $level <= $maxLevel; $level++) { // Loop over more levels, if specified (first level included by default)
                $levelName = (new AreaTree(removeLastNLevels: 1))->hierarchies[$level];
                $areaCodes = Area::ofLevel($level)->pluck('code');
                foreach ($areaCodes as $code) {
                    (new IndicatorCaching($indicator, [$levelName => $code]))->update();
                }
                $this->info(" - cached $levelName level");
            }
            $endTime = time();
            $this->info("Completed in " . ($endTime - $startTime) . " seconds");
        }
        $this->newLine();
    }

    private function cacheScorecards()
    {
        if ($this->option('questionnaire')) {
            $scorecardsToCache = Scorecard::ofQuestionnaire($this->option('questionnaire'))->published()->get();
        } else {
            $scorecardsToCache = Scorecard::published()->get();
        }

        foreach ($scorecardsToCache as $scorecard) {
            $this->newLine()->info($scorecard->name);
            $startTime = time();
            (new ScorecardCaching($scorecard, []))->update();
            $endTime = time();
            $this->info("Completed in " . ($endTime - $startTime) . " seconds");
        }
        $this->newLine();
    }

    private function cacheMapIndicators()
    {
        if ($this->option('questionnaire')) {
            $mapIndicatorsToCache = MapIndicator::ofQuestionnaire($this->option('questionnaire'))->published()->get();
        } else {
            $mapIndicatorsToCache = MapIndicator::published()->get();
        }

        foreach ($mapIndicatorsToCache as $mapIndicator) {
            $this->newLine()->info($mapIndicator->name);
            $startTime = time();
            (new MapIndicatorCaching($mapIndicator, []))->update();
            $endTime = time();
            $this->info("Completed in " . ($endTime - $startTime) . " seconds");
        }
        $this->newLine();
    }

    public function handle()
    {
        $this->cacheIndicators();
        $this->cacheScorecards();
        //$this->cacheMapIndicators();
        return Command::SUCCESS;
    }
}
