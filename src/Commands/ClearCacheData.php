<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\Caching;

class ClearCacheData extends Command
{
    protected $signature = 'chimera:cache-clear {--questionnaire=}';

    protected $description = "Clear all cached data";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('questionnaire')) {
            Cache::tags([$this->option('questionnaire'), 'map-indicators'])->flush();
        } else {
            Cache::tags(['indicators', 'scorecards', 'map-indicators'])->flush();
        }

        $this->newLine()->info("The cache has been cleared");
        $this->newLine();
        return Command::SUCCESS;
    }
}
