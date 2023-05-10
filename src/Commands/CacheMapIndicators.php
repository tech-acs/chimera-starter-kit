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

class CacheMapIndicators extends Command
{
    protected $signature = 'chimera:cache-mapindicators {--max-level=0} {--questionnaire=} {--tag=}';

    protected $description = "Calculate and cache (published) map indicators";

    public function __construct()
    {
        parent::__construct();
    }

    private function cacheMapIndicators()
    {
        if ($this->option('tag')) {
            $builder = MapIndicator::published()->ofTag($this->option('tag'));
        } else {
            $builder = MapIndicator::published()->untagged();
        }

        if ($this->option('questionnaire')) {
            $indicatorsToCache = $builder->ofQuestionnaire($this->option('questionnaire'))->get();
        } else {
            $indicatorsToCache = $builder->get();
        }

        if ($indicatorsToCache->isEmpty()) {
            $this->newLine()->error('No matching map indicators found');
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

            $analytics = ['source' => 'Caching (cmd)', 'level' => null, 'started_at' => time(), 'completed_at' => null];
            $updated = (new MapIndicatorCaching($indicator, []))->update(); // National level - no filters (non-level)
            if ($updated) {
                $analytics['completed_at'] = time();
                $indicator->analytics()->create($analytics);
            } else {
                $this->error("Could not update cache!");
            }

            for ($level = 0; $level <= $maxLevel; $level++) { // Loop over more levels, if specified (first level included by default)
                $levelName = (new AreaTree())->hierarchies[$level];
                $areaCodes = Area::ofLevel($level)->pluck('code');
                foreach ($areaCodes as $code) {
                    $analytics = ['source' => 'Caching (cmd)', 'level' => $level, 'started_at' => time(), 'completed_at' => null];
                    $updated = (new MapIndicatorCaching($indicator, [$levelName => $code]))->update();
                    if ($updated) {
                        $analytics['completed_at'] = time();
                        $indicator->analytics()->create($analytics);
                    } else {
                        $this->error("Could not update cache!");
                    }
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
        return $this->cacheMapIndicators();
    }
}
