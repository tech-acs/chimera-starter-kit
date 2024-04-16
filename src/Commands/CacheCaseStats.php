<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Uneca\Chimera\Models\DataSource;
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
            return Command::FAILURE;
        }

        foreach ($toCache as $dataSource) {
            $this->newLine()->info($dataSource->name);
            $startTime = time();

            $analytics = ['source' => 'Caching (cmd)', 'level' => null, 'started_at' => time(), 'completed_at' => null];
            $updated = (new CaseStatsCaching($dataSource, []))->update();
            if ($updated) {
                $analytics['completed_at'] = time();
                $dataSource->analytics()->create($analytics);
                $endTime = time();
                $this->info("Completed in " . ($endTime - $startTime) . " seconds");
            } else {
                $this->error("Could not update cache!");
            }
        }
        $this->newLine();
        return Command::SUCCESS;
    }

    public function handle()
    {
        return $this->cacheCaseStats();
    }
}
