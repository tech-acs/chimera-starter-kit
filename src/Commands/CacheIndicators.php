<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\DashboardComponentFactory;
use Uneca\Chimera\Services\FetchCacheAndRecord;

class CacheIndicators extends Command
{
    protected $signature = 'chimera:cache-indicators {--max-level=0} {--data-source=} {--tag=}';
    protected $description = "Calculate and cache (published) indicators";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('tag')) {
            $builder = Indicator::published()->ofTag($this->option('tag'));
        } else {
            $builder = Indicator::published()->untagged();
        }

        if ($this->option('data-source')) {
            $indicatorsToCache = $builder->ofDataSource($this->option('data-source'))->get();
        } else {
            $indicatorsToCache = $builder->get();
        }

        if ($indicatorsToCache->isEmpty()) {
            $this->newLine()->error('No matching indicators found');
            $this->newLine();
            return self::FAILURE;
        }

        $maxLevel = $this->option('max-level') ?? 0;
        if ($maxLevel > 0 && ($maxLevel >= AreaHierarchy::count())) {
            $maxLevel = AreaHierarchy::count() - 1;
        }

        foreach ($indicatorsToCache as $indicator) {
            $this->newLine()->info($indicator->name);

            $artefact = DashboardComponentFactory::makeScorecard($indicator);

            // National level for non-restricted users
            $startTime = time();
            (new FetchCacheAndRecord)($artefact, $artefact->cacheKey(), '', true);
            $this->info("Level 0 completed in " . (time() - $startTime) . " seconds");

            $hierarchies = (new AreaTree())->hierarchies;
            for ($level = 0; $level <= $maxLevel; $level++) { // Loop over more levels, if specified (first level included by default)
                $paths = Area::ofLevel($level)->pluck('path');
                foreach ($paths as $path) {
                    (new FetchCacheAndRecord)($artefact, $artefact->cacheKey(), $path, true);
                }
                $this->info(" - cached {$hierarchies[$level]} level");
            }
            $this->info("Completed in " . (time() - $startTime) . " seconds");
        }
        $this->newLine();
        return self::SUCCESS;
    }
}
