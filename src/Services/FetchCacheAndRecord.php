<?php

namespace Uneca\Chimera\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Livewire\CaseStats;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Livewire\ScorecardComponent;

class FetchCacheAndRecord
{
    public function __invoke(CaseStats|ScorecardComponent|Chart $artefact, string $key, string $filterPath, bool $cacheForever = false)
    {
        try {
            $startTime = time();
            $result = $artefact->getData($filterPath);
            $elapsedSeconds = time() - $startTime;

            if ($cacheForever) {
                Cache::put($key, [Carbon::now(), $result]);
            } else {
                Cache::put($key, [Carbon::now(), $result], config('chimera.cache.ttl'));
            }

            if ($elapsedSeconds > config('chimera.long_query_time')) {
                $artefact->getDataModel()->analytics()->create([
                    'user_id' => auth()->id(),
                    'path' => $filterPath,
                    'started_at' => Carbon::createFromTimestamp($startTime),
                    'elapsed_seconds' => $elapsedSeconds
                ]);
            }
        } catch (\Exception $exception) {
            logger('Something bad happened in FetchCacheAndRecord invokable', ['Exception' => $exception->getMessage()]);
        }
    }
}
