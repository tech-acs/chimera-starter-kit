<?php

namespace App\Http\Livewire;

use App\Services\Area;
use App\Services\AreaListFactory;
use App\Services\Caching;
use App\Services\Traits\ChecksumSafetyTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AreaFilter extends Component
{
    use ChecksumSafetyTrait;

    public ?string $connection = null;
    public Collection $areas;
    public array $selections = [];
    public array $areaRestrictions = [];

    public function mount()
    {
        $areaRepository = new Area($this->connection);
        $levels = $areaRepository->levels();
        $levels->pop(); // We do not have a select for the last level (EA). So, drop it!
        $sessionFilter = session()->get($this->connection, []);
        $this->areas = collect([]);
        $parentLevel = null;
        foreach ($levels as $levelName => $level) {
            if (is_null($parentLevel)) {
                $this->areas[$levelName] = $areaRepository->areas()->pluck('name', 'code')->all();
            } else {
                $parent = $sessionFilter[$parentLevel] ?? null;
                $this->areas[$levelName] = $parent ?
                    $areaRepository->areas($this->removeChecksumSafety($parent), type: $levelName)->pluck('name', 'code')->all() :
                    [];
            }
            $parentLevel = $levelName;
        }
        $this->selections = $levels->map(fn ($level, $levelName) => $this->addChecksumSafety($sessionFilter[$levelName] ?? null))->all();
    }

    public function changeHandler($changedSelect, $selected)
    {
        $this->selections[$changedSelect] = $selected;
        $next = $this->nextKey($changedSelect);
        if ($next) {
            $this->areas[$next] = (new Area($this->connection))->areas($this->removeChecksumSafety($selected), type: $next)->pluck('name', 'code')->all();
            $nextKeys = $this->nextKeys($changedSelect);
            foreach ($nextKeys as $key) {
                if ($key !== $next) {
                    $this->areas[$key] = [];
                }
                $this->selections[$key] = null;
            }
        }
    }

    public function filter()
    {
        $selections = collect($this->selections)->filter();
        $selectionNames = $selections->mapWithKeys(fn ($code, $type) => [$type.'Name' => $this->areas[$type][$code]])->all();
        $selectionCodes = $selections->map(fn ($code) => $this->removeChecksumSafety($code))->all();
        $filter = [...$selectionNames, ...$selectionCodes];
        session()->put($this->connection, $filter);
        $this->emit('updateChart', $filter);
    }

    public function clear()
    {
        session()->forget($this->connection);
        $this->mount();
        $this->emit('updateChart', []);
    }

    public function render()
    {
        return view('livewire.area-filter');
    }

    private function nextKey($currentKey)
    {
        $keys = $this->areas->keys();
        $currentKeyIndex = $keys->search($currentKey);
        return $keys[$currentKeyIndex + 1] ?? null;
    }

    private function nextKeys($currentKey)
    {
        $keys = $this->areas->keys();
        $currentKeyIndex = $keys->search($currentKey);
        return array_slice($keys->all(), $currentKeyIndex + 1);
    }

    public function getAreaListWithCaching($connection, $areaType, $parentArea)
    {
        if (config('chimera.cache.enabled')) {
            $key = Caching::makeAreaListCacheKey($connection, $areaType, $parentArea);
            return Cache::tags([$connection, 'area-list'])
                ->rememberForever($key, function () use ($connection, $areaType, $parentArea) {
                    return AreaListFactory::make($this->connection)->getAreaList($this->connection, $areaType, $this->removeChecksumSafety($parentArea));
                });
        }
        return AreaListFactory::make($this->connection)->getAreaList($this->connection, $areaType, $this->removeChecksumSafety($parentArea));
    }
}
