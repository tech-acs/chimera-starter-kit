<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Component;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Services\AreaTree;

class LiveSearch extends Component
{
    public $query = '';
    public $results = [];
    public $selectedResult = '';
    public $removeLastNLevels = 1;
    public $excludedLevels = [];
    public $restrictedPath = '';

    public function mount()
    {
        $this->excludedLevels = app('hierarchies')
            ->take(-1 * $this->removeLastNLevels)
            ->keys()
            ->toArray();
        $this->restrictedPath = AreaTree::getFinestResolutionFilterPath(auth()->user()->areaRestrictionAsFilter());
    }

    public function updatedQuery()
    {
        $this->results = [];
        if (strlen($this->query) < 2) {
            return;
        }
        $locale = app()->getLocale();
        $this->results = Area::select('path', 'name', 'level')
            ->where("name->{$locale}", 'ilike', $this->query . '%')
            ->when(! empty($this->excludedLevels), function ($query) {
                $query->whereNotIn('level', $this->excludedLevels);
            })
            ->when(! empty($this->restrictedPath), function ($query) {
                $query->whereRaw("path <@ '{$this->restrictedPath}'");
            })
            ->orderBy('level')
            ->take(10)
            ->get()
            ->map(function (Area $area) {
                $area->displayName = $area->name . ' (' . app('hierarchies')[$area->level] . ')';
                return $area;
            });
    }

    public function selectResult($path, $name)
    {
        $this->selectedResult = $path;
        $this->query = $name;
        $this->results = [];
    }

    public function apply()
    {
        $this->dispatch('searchedAreaUpdated', path: $this->selectedResult);
    }

    public function clear()
    {
        $this->query = '';
        $this->selectedResult = '';
        $this->dispatch('searchedAreaCleared');
    }

    public function render()
    {
        return view('chimera::livewire.live-search');
    }
}
