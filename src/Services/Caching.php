<?php

namespace Uneca\Chimera\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Models\Scorecard;

abstract class Caching
{
    public Model $model;
    public ?Object $instance;
    public array $filter;
    public string $key;

    abstract public function __construct(Scorecard|MapIndicator|Indicator|DataSource $model, array $filter);

    abstract public function tags(): array;

    public function update(): bool
    {
        if (is_null($this->instance)) {
            return false;
        }
        try {
            $freshData = $this->instance->getData($this->filter);
            $this->stamp();
            return Cache::tags($this->tags())->put($this->key, $freshData);
        } catch (\Exception $exception) {
            dump($exception->getMessage());
            return false;
        }
    }

    public function stamp(): bool
    {
        return Cache::tags(['timestamps'])->put("$this->key|timestamp", Carbon::now());
    }

    public function getTimestamp(): Carbon
    {
        return Cache::tags(['timestamps'])->get("$this->key|timestamp", Carbon::now());
    }
}
