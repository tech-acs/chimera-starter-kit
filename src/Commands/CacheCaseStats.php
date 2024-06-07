<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\DashboardComponentFactory;
use Uneca\Chimera\Services\FetchCacheAndRecord;

class CacheCaseStats extends Command
{
    protected $signature = 'chimera:cache-casestats {--data-source=}';

    protected $description = "Calculate and cache (active and shown-on-home-page data source) case stats";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $query = DataSource::active()->showOnHomePage();
        if ($this->option('data-source')) {
            $toCache = $query->where('name', $this->option('data-source'))->get();
        } else {
            $toCache = $query->get();
        }

        if ($toCache->isEmpty()) {
            $this->newLine()->error('No matching case-stats found');
            $this->newLine();
            return self::FAILURE;
        }

        foreach ($toCache as $dataSource) {
            $this->newLine()->info($dataSource->name);

            $artefact = DashboardComponentFactory::makeCaseStats($dataSource);

            // National level for non-restricted users (filter = [])
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
