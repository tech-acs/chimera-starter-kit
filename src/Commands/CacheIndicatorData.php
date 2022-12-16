<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\Caching;
use Uneca\Chimera\Services\IndicatorFactory;

class CacheIndicatorData extends Command
{
    protected $signature = 'chimera:cache {--max-level=0}';

    protected $description = "Calculate and cache indicator data";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $publishedIndicators = Indicator::published()->get();
        $maxLevel = $this->option('max-level') ?? 0;
        // Make sure $maxLevel <= AreaHierarchy::count()
        foreach ($publishedIndicators as $indicator) {
            $startTime = time();
            Caching::updateIndicatorCache($indicator); // Level 0
            for ($level = 0; $level <= $maxLevel; $level++) { // Loop over more levels, if specified
                $levelName = (new AreaTree(removeLastNLevels: 1))->hierarchies[$level];
                $areaCodes = Area::ofLevel($level)->pluck('code');
                foreach ($areaCodes as $code) {
                    Caching::updateIndicatorCache($indicator, [$levelName => $code]);
                }
                $this->info("Completed caching $levelName level");
            }
            $endTime = time();

            $this->info("Took " . ($endTime - $startTime) . " seconds to update $indicator->slug");
        }

        return Command::SUCCESS;
    }
}
