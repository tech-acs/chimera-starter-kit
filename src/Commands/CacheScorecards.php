<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\ScorecardCaching;

class CacheScorecards extends Command
{
    protected $signature = 'chimera:cache-scorecards {--data-source=}';

    protected $description = "Calculate and cache (published) scorecards";

    public function __construct()
    {
        parent::__construct();
    }

    private function cacheScorecards()
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
            $startTime = time();

            // National level for non-restricted users (filter = [])
            $analytics = ['source' => 'Caching (cmd)', 'level' => null, 'started_at' => time(), 'completed_at' => null];
            $updated = (new ScorecardCaching($scorecard, []))->update();
            if ($updated) {
                $analytics['completed_at'] = time();
                $scorecard->analytics()->create($analytics);
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
                $updated = (new ScorecardCaching($scorecard, $filter))->update();
                if ($updated) {
                    $analytics['completed_at'] = time();
                    $scorecard->analytics()->create($analytics);
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
        return $this->cacheScorecards();
    }
}
