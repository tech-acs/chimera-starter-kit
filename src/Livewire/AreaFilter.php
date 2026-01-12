<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Attributes\On;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Traits\ChecksumSafetyTrait;
use Livewire\Component;

class AreaFilter extends Component
{
    use ChecksumSafetyTrait;

    public array $dropdowns;

    public int $removeLastNLevels = 1;

    public string $sessionKey = 'area-filter';

    public string $changeEvent = 'filterChanged';

    public string $mode = 'select';

    public function mount()
    {
        $areaTree = new AreaTree(removeLastNLevels: $this->removeLastNLevels);
        $selectionsFromSession = session()->get($this->sessionKey, []);
        $restrictions = auth()->user()->areaRestrictionAsFilter();
        $subject = null;
        $this->dropdowns = array_map(function ($level) use ($selectionsFromSession, $restrictions, $areaTree, &$subject) {
            $dropdown = ['list' => [], 'selected' => null, 'restricted' => null];
            $levelName = $areaTree->hierarchies[$level];
            if ($level === 0) {
                $dropdown['list'] = $areaTree->areas(checksumSafe: true)->pluck('name', 'path')->all();
            }
            if ($subject) {
                $dropdown['list'] = $areaTree->areas($subject, checksumSafe: true)->pluck('name', 'path')->all();
                $subject = null;
            }
            if (array_key_exists($levelName, $selectionsFromSession)) {
                $subject = $selectionsFromSession[$levelName];
                $dropdown['selected'] = $this->addChecksumSafety($subject);
            }
            if (array_key_exists($levelName, $restrictions)) {
                $subject = $restrictions[$levelName];
                $dropdown['restricted'] = $this->addChecksumSafety($subject);
            }
            return $dropdown;
        }, array_flip($areaTree->hierarchies));
    }

    public function switchMode()
    {
        $this->mode = $this->mode === 'select' ? 'search' : 'select';
    }

    public function changeHandler($changedLevelName, $selectedPath)
    {
        $areaTree = new AreaTree(removeLastNLevels: 1);
        $shouldUpdate = false;
        $resetDownstream = false;
        foreach ($this->dropdowns as $levelName => $dropdown) {
            if ($resetDownstream) {
                $this->dropdowns[$levelName]['list'] = [];
                $this->dropdowns[$levelName]['selected'] = null;
                continue;
            }
            if ($shouldUpdate) {
                $this->dropdowns[$levelName]['list'] = $areaTree
                    ->areas($this->removeChecksumSafety($selectedPath), checksumSafe: true)
                    ->pluck('name', 'path')
                    ->all();
                $this->dropdowns[$levelName]['selected'] = null;
                $shouldUpdate = false;
                $resetDownstream = true;
            }
            if ($changedLevelName === $levelName) {
                $this->dropdowns[$changedLevelName]['selected'] = $selectedPath;
                $shouldUpdate = true;
            }
        }
    }

    public function filter()
    {
        $filter = array_map(
            fn ($dropdown) => $this->removeChecksumSafety($dropdown['selected']),
            array_filter($this->dropdowns, fn ($dropdown) => $dropdown['selected'])
        );
        session()->put($this->sessionKey, $filter);
        $this->dispatch($this->changeEvent);
    }

    #[On(['searchedAreaUpdated'])]
    public function applySearchFilter($path)
    {
        $filter = AreaTree::pathAsFilter($path, returnedColumn: 'path');
        session()->put($this->sessionKey, $filter);
        $this->mount();
        $this->dispatch($this->changeEvent);
    }

    #[On(['searchedAreaCleared'])]
    public function clear()
    {
        session()->forget($this->sessionKey);
        $this->mount();
        $this->dispatch($this->changeEvent);
    }

    public function render()
    {
        return view('chimera::livewire.area-filter');
    }
}
