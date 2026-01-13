<?php

namespace Uneca\Chimera\Livewire;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;
use Uneca\Chimera\Enums\DataStatus;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Traits\AreaResolver;
use Uneca\Chimera\Traits\Cachable;

abstract class GaugeComponent extends Component
{
    use Cachable;
    use AreaResolver;

    public Gauge $gauge;
    public string $title;
    public string $subtitle;
    public int|float|string $value = '';
    public string $unit = '%';
    public int $outOf = 100;
    public array $colorThresholds = [70 => 'text-red-500', 90 => 'text-amber-500', 101 => 'text-green-500'];
    public string $scoreColor = 'text-gray-500';
    public Carbon $dataTimestamp;
    public string $placement = 'area-insights';

    public function mount()
    {
        $this->title = $this->gauge->title;
        $this->subtitle = $this->gauge->subtitle;

        $this->resolveAreaAndCheckData();
    }

    private function resolveAreaAndCheckData()
    {
        list($this->filterPath,) = $this->areaResolver();
        if ($this->gauge->supportsLevel($this->filterPath)) {
            $this->checkData();
        } else {
            $this->dataStatus = DataStatus::INAPPLICABLE->value;
        }
    }

    #[On(['areaInsightsfilterChanged'])]
    public function update()
    {
        $this->resolveAreaAndCheckData();
    }

    public function placeholder()
    {
        return view('chimera::livewire.placeholders.gauge');
    }

    public function cacheKey(): string
    {
        return implode(':', ['gauge', $this->gauge->id, $this->filterPath]);
    }

    public function assignColor($value)
    {
        return collect($this->colorThresholds)
            ->first(fn($color, $threshold) => $value <= $threshold, 'text-gray-500');
    }

    public function setPropertiesFromData(): void
    {
        list($this->dataTimestamp, $data) = Cache::get($this->cacheKey());
        if ($data instanceof Collection) {
            $this->value = $data->first()->value;
            $this->scoreColor = $this->assignColor($this->value);
            $this->dataStatus = DataStatus::RENDERABLE->value;
        } else {
            $this->dataStatus = DataStatus::EMPTY->value;
        }
    }

    public function getDataModel(): Model
    {
        return $this->gauge;
    }

    public function render()
    {
        return view('chimera::livewire.gauge');
    }
}
