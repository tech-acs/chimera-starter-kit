<?php

namespace App\Services\Traits;

use App\Services\Caching;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

trait Cachable
{
    abstract public function getCollection(array $filter);

    abstract protected function setData(array $filter = []);

    public function getData(array $filter, string $connection = null, $indicator = null)
    {
        $connection = $connection ?? $this->connection;
        $indicator = $indicator ?? $this->graphDiv;
        try {
            DB::connection($connection);
            if (config('chimera.cache.enabled')) {
                $key = Caching::makeIndicatorCacheKey($indicator, $filter);
                $this->dataTimestamp = Cache::tags([$this->connection, 'timestamp'])->get($key, 'Unknown');
                return Cache::tags([$connection, 'indicator'])
                    ->rememberForever($key, function () use ($connection, $indicator, $filter) {
                        return $this->getCollection($filter);
                    });
            }
            return $this->getCollection($filter);
        } catch (\Exception $exception) {
            logger("Exception occurred in getData()", ['Exception: ' => $exception]);
            return collect([]);
        }
    }

    public function update(array $filter)
    {
        $this->setData($filter);
        $this->setLayout($filter);
        $this->emit("redrawChart-{$this->graphDiv}", $this->data, $this->layout);
    }
    
    protected function getColumnArray()
    {
        return (new Collection)
        ->push(
            [
                'title'      => "Default Title",
                'data'       => "default_name",
                'sortable'   => true,
            ]);
    }
}
