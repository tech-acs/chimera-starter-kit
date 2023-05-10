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

            $analytics = ['source' => 'Caching (cmd)', 'level' => null, 'started_at' => time(), 'completed_at' => null];
            $updated = (new IndicatorCaching($indicator, []))->update(); // National level - no filters (non-level/null level)
            if ($updated) {
                $analytics['completed_at'] = time();
                $indicator->analytics()->create($analytics);
            } else {
                $this->error("Could not update cache!");
            }

            $hierarchies = (new AreaTree())->hierarchies;
            for ($level = 0; $level <= $maxLevel; $level++) { // Loop over more levels, if specified (first level included by default)
                $paths = Area::ofLevel($level)->pluck('path');
                foreach ($paths as $path) {
                    $codes = explode('.', $path);
                    $filter = [];
                    for ($i = 0; $i < count($codes); $i++) {
                        $filter[$hierarchies[$i]] = $codes[$i];
                    }
                    $analytics = ['source' => 'Caching (cmd)', 'level' => $level, 'started_at' => time(), 'completed_at' => null];
                    $updated = (new IndicatorCaching($indicator, $filter))->update(); // Sub-national level
                    if ($updated) {
                        $analytics['completed_at'] = time();
                        $indicator->analytics()->create($analytics);
                    } else {
                        $this->error("Could not update cache!");
                    }
                }
                $this->info(" - cached {$hierarchies[$level]} level");
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
