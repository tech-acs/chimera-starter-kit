<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\IndicatorCaching;

class CacheIndicators extends Command
{
    protected $signature = 'chimera:cache-indicators {--max-level=0} {--questionnaire=} {--tag=}';

    protected $description = "Calculate and cache (published) indicators";

    public function __construct()
    {
        parent::__construct();
    }

    private function cacheIndicators()
    {
        if ($this->option('tag')) {
            $builder = Indicator::published()->ofTag($this->option('tag'));
        } else {
            $builder = Indicator::published()->untagged();
        }

        if ($this->option('questionnaire')) {
            $indicatorsToCache = $builder->ofQuestionnaire($this->option('questionnaire'))->get();
        } else {
            $indicatorsToCache = $builder->get();
        }

        if ($indicatorsToCache->isEmpty()) {
            $this->newLine()->error('No matching indicators found');
            $this->newLine();
            return Command::FAILURE;
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
        return Command::SUCCESS;
    }

    public function handle()
    {
        return $this->cacheIndicators();
    }
}
