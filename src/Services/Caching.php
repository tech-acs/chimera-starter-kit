<?php

namespace Uneca\Chimera\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Scorecard;

abstract class Caching
{
    public Model $model;
    public Object $instance;
    public array $filter;
    public string $key;

    abstract public function __construct(Scorecard|MapIndicator|Indicator $model, array $filter);

    abstract public function tags(): array;

    public function update(): bool
    {
        $freshData = $this->instance->getData($this->filter);
        $this->stamp();
        return Cache::tags($this->tags())->put($this->key, $freshData);
    }

    public function stamp(): bool
    {
        return Cache::tags(['timestamps'])->put("$this->key|timestamp", time());
    }

    public function getTimestamp(): int
    {
        return Cache::tags(['timestamps'])->get("$this->key|timestamp", time());
    }
}
