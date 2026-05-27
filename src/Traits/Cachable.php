<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Enums\DataStatus;
use Uneca\Chimera\Jobs\QueryRunnerJob;

trait Cachable
{
    public string $dataStatus = DataStatus::PENDING->value;

    public string $filterPath = '';

    abstract public function getData(string $filterPath): Collection;

    abstract public function cacheKey(): string;

    abstract public function setPropertiesFromData(): void;

    public function getDataAndCacheIt(string $key, string $filterPath, bool $cacheForever = false): void
    {
        QueryRunnerJob::dispatch($this, $key, $filterPath, $cacheForever);
    }

    public function checkData(): void
    {
        if (Cache::has($this->cacheKey())) {
            $this->setPropertiesFromData();
            $this->dispatch('dataReady')->self();
        } else {
            $this->getDataAndCacheIt($this->cacheKey(), $this->filterPath);
        }
    }

    abstract public function getDataModel(): Model;
}
