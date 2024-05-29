<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\CaseStatsCaching;

class CacheCaseStats extends Command
{
    protected $signature = 'chimera:cache-casestats {--data-source=}';

    protected $description = "Calculate and cache (active and shown-on-home-page data source) case stats";

    public function __construct()
    {
        parent::__construct();
    }

    private function cacheCaseStats()
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
            $startTime = time();

            // National level for non-restricted users (filter = [])
            $analytics = ['source' => 'Caching (cmd)', 'level' => null, 'started_at' => time(), 'completed_at' => null];
            $updated = (new CaseStatsCaching($dataSource, []))->update();
            if ($updated) {
                $analytics['completed_at'] = time();
                $dataSource->analytics()->create($analytics);
                $endTime = time();
                $this->info("Level 0 completed in " . ($endTime - $startTime) . " seconds");
            } else {
                $this->error("Could not update cache!");
            }
            // Get all user area restrictions and loop them as filter
            $paths = AreaRestriction::distinct('path')->pluck('path');
            foreach ($paths as $path) {
                $filter = AreaTree::pathAsFilter($path);
                $analytics = ['source' => 'Caching (cmd)', 'level' => null, 'started_at' => time(), 'completed_at' => null];
                $updated = (new CaseStatsCaching($dataSource, $filter))->update();
                if ($updated) {
                    $analytics['completed_at'] = time();
                    $dataSource->analytics()->create($analytics);
                    $endTime = time();
                    $this->info("Restriction path $path completed in " . ($endTime - $startTime) . " seconds");
                } else {
                    $this->error("Could not update cache!");
                }
            }
        }
        $this->newLine();
        return self::SUCCESS;
    }

    public function handle()
    {
        return $this->cacheCaseStats();
    }
}
