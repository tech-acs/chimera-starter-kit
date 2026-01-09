<?php

namespace Uneca\Chimera\Livewire;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Services\DashboardComponentFactory;

class CacheClearer extends Component
{
    public string $artefact;
    public int $id;

    private function makeArtefact()
    {
        return match ($this->artefact) {
            'indicator' => DashboardComponentFactory::makeIndicator(Indicator::find($this->id)),
            'scorecard' => DashboardComponentFactory::makeScorecard(Scorecard::find($this->id)),
            'gauge' => DashboardComponentFactory::makeGauge(Gauge::find($this->id)),
        };
    }

    public function clear()
    {
        $artefact = $this->makeArtefact();
        Cache::forget($artefact->cacheKey());
        $this->dispatch('notify', content: 'Cache cleared successfully for this artefact', type: 'success');
    }

    public function render()
    {
        return view('chimera::livewire.cache-clearer');
    }
}
