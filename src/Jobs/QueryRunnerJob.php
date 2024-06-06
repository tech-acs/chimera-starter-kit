<?php

namespace Uneca\Chimera\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Livewire\CaseStats;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Livewire\ScorecardComponent;

class QueryRunnerJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    // ToDo: This needs to be raised
    public $timeout = 600;
    public $uniqueFor = 600;

    public function __construct(
        private readonly CaseStats|ScorecardComponent|Chart $artefact,
        private readonly string $key,
        private readonly string $filterPath,
        private readonly bool $cacheForever = false
    )
    {}

    public function handle()
    {
        //sleep(2);

        $startTime = time();
        $result = $this->artefact->getData($this->filterPath);
        $elapsedSeconds = time() - $startTime;

        if ($this->cacheForever) {
            Cache::put($this->key, [Carbon::now(), $result]);
        } else {
            Cache::put($this->key, [Carbon::now(), $result], config('chimera.cache.ttl'));
        }

        if ($elapsedSeconds > config('chimera.long_query_time')) {
            $this->artefact->analytics()->create([
                'user_id' => null,
                'path' => $this->filterPath,
                'started_at' => Carbon::createFromTimestamp($startTime),
                'elapsed_seconds' => $elapsedSeconds
            ]);
        }
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
