<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\DashboardComponentFactory;
use Uneca\Chimera\Services\FetchCacheAndRecord;

class CacheScorecards extends Command
{
    protected $signature = 'chimera:cache-scorecards {--data-source=}';

    protected $description = "Calculate and cache (published) scorecards";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('data-source')) {
            $scorecardsToCache = Scorecard::ofDataSource($this->option('data-source'))->published()->get();
        } else {
            $scorecardsToCache = Scorecard::published()->get();
        }

        if ($scorecardsToCache->isEmpty()) {
            $this->newLine()->error('No matching scorecards found');
            $this->newLine();
            return self::FAILURE;
        }

        foreach ($scorecardsToCache as $scorecard) {
            $this->newLine()->info($scorecard->name);

            $artefact = DashboardComponentFactory::makeScorecard($scorecard);

            // National level for non-restricted users
            $startTime = time();
            (new FetchCacheAndRecord)($artefact, $artefact->cacheKey(), '', true);
            $this->info("Level 0 completed in " . (time() - $startTime) . " seconds");

            // Get all user area restrictions and loop them as filter
            $paths = AreaRestriction::distinct('path')->pluck('path');
            foreach ($paths as $path) {
                $startTime = time();
                (new FetchCacheAndRecord)($artefact, $artefact->cacheKey(), $path, true);
                $this->info("Restriction path $path completed in " . (time() - $startTime) . " seconds");
            }
        }
        $this->newLine();
        return self::SUCCESS;
    }
}
