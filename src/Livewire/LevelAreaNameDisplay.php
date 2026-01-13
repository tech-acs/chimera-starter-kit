<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Uneca\Chimera\Services\AreaTree;
use \Uneca\Chimera\Traits\AreaResolver;

class LevelAreaNameDisplay extends Component
{
    use AreaResolver;

    public string $placement = 'area-insights';
    public string $filterPath = '';
    public string $areaName = '';
    public string $levelName = '';

    public function mount()
    {
        $this->resolveDisplayName();
    }

    private function resolveDisplayName()
    {
        list($this->filterPath,) = $this->areaResolver();
        if (empty($this->filterPath)) {
            $this->levelName = 'National';
            $this->areaName = '';
        } else {
            $area = (new AreaTree)->getArea($this->filterPath);
            $this->levelName = app('hierarchies')[$area->level];
            $this->areaName = $area->name;
        }
    }

    #[On(['filterChanged', 'areaInsightsfilterChanged'])]
    public function update()
    {
        $this->resolveDisplayName();
    }

    public function render()
    {
        return view('chimera::livewire.level-area-name-display');
    }
}
