<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Enums\DataStatus;
use Uneca\Chimera\Jobs\QueryRunnerJob;

trait Cachable
{
    public DataStatus $dataStatus = DataStatus::PENDING;
    public string $filterPath = '';

    public abstract function getData(string $filterPath): Collection;

    public abstract function cacheKey(): string;

    public abstract function setPropertiesFromData(): void;

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
            $this->dataStatus = DataStatus::PENDING;
            $this->getDataAndCacheIt($this->cacheKey(), $this->filterPath);
        }
    }

    public abstract function getDataModel(): Model;
}
