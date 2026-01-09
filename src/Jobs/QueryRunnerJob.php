<?php

namespace Uneca\Chimera\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Uneca\Chimera\Livewire\CaseStats;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Livewire\GaugeComponent;
use Uneca\Chimera\Livewire\ScorecardComponent;
use Uneca\Chimera\Services\FetchCacheAndRecord;

class QueryRunnerJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    // ToDo: This needs to be raised
    public $timeout = 600;
    public $uniqueFor = 600;

    public function __construct(
        private readonly CaseStats|ScorecardComponent|GaugeComponent|Chart $artefact,
        private readonly string $key,
        private readonly string $filterPath,
        private readonly bool $cacheForever = false
    )
    {}

    public function handle()
    {
        (new FetchCacheAndRecord)($this->artefact, $this->key, $this->filterPath, $this->cacheForever);
    }

    public function uniqueId(): string
    {
        return $this->key;
    }

    public function failed(\Throwable $exception)
    {
        logger('QueryRunner Job Failed', ['Exception: ' => $exception->getMessage()]);
    }
}
