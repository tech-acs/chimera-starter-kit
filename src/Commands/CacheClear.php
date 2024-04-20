<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\Caching;

class CacheClear extends Command
{
    protected $signature = 'chimera:cache-clear {--data-source=} {--type=}';

    protected $description = "Clear cached data";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('data-source')) {
            Cache::tags([$this->option('data-source')])->flush();
        } elseif ($this->option('type')) { // indicators|scorecards|casestats|mapindicators
            Cache::tags([$this->option('type')])->flush();
        } else {
            Cache::flush();
        }

        $this->newLine()->info("The cache has been cleared");
        $this->newLine();
        return self::SUCCESS;
    }
}
