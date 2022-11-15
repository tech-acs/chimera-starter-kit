<?php

namespace App\Http\Livewire;

use App\Services\AreaTree;
use App\Services\Traits\ChecksumSafetyTrait;
use Livewire\Component;

class AreaFilter extends Component
{
    use ChecksumSafetyTrait;

    public array $hierarchies;  // [ 0 => 'region',  1 => 'constituency', ... ]
    public array $dropdowns;    // [ 'region' => [path1 => name1, path2 => name2, ...] ]
    public array $selections;   // [ 'region' => '*02', 'regionName' => 'Erongo', ... ]
    public array $restrictions; // [ 'region' => '*02', ... ]

    public function mount()
    {
        $areaTree = new AreaTree(removeLastNLevels: 1);
        $this->hierarchies = $areaTree->hierarchies;
        $selectionsFromSession = session()->get('area-filter', []);
        $restrictions = []; //['region' => '02', 'constituency' => '02.0201'];
        $heldPath = null;
        $this->dropdowns = array_map(function ($level) use ($selectionsFromSession, $restrictions, $areaTree) {
            global $heldPath;
            $levelName = $this->hierarchies[$level];
            $replacementValue = [];
            if ($level === 0) {
                $replacementValue = $areaTree->areas()->pluck('name', 'path')->all();
            }
            if ($heldPath) {
                $replacementValue = $areaTree->areas($heldPath)->pluck('name', 'path')->all();
                $heldPath = null;
            }
            if (array_key_exists($levelName, $selectionsFromSession)) {
                $heldPath = $selectionsFromSession[$levelName];
            }
            if (array_key_exists($levelName, $restrictions)) {
                $heldPath = $restrictions[$levelName];
            }
            return $replacementValue;
        }, array_flip($this->hierarchies));
        //dump($selectionsFromSession, $restrictions);
        $this->selections = array_map(fn ($level) => $this->addChecksumSafety($selectionsFromSession[$this->hierarchies[$level]] ?? null), array_flip($this->hierarchies));
        $this->restrictions = array_map(fn ($path) => $this->addChecksumSafety($path), $restrictions);
        if (! empty($this->restrictions)) {
            $this->selections = array_replace($this->selections, $this->restrictions);
        }
    }

    public function changeHandler($changedLevelName, $selectedPath)
    {
        $areaTree = new AreaTree(removeLastNLevels: 1);
        $this->selections[$changedLevelName] = $selectedPath;
        $nextDropdown = $areaTree->next($changedLevelName);
        if ($nextDropdown) {
            $this->dropdowns[$nextDropdown] = $areaTree
                ->areas($this->removeChecksumSafety($selectedPath))
                ->pluck('name', 'path')
                ->all();
            $nextLevelNames = $areaTree->nextLevelNames($changedLevelName);
            foreach ($nextLevelNames as $levelName) {
                if ($levelName !== $nextDropdown) {
                    $this->dropdowns[$levelName] = [];
                }
                $this->selections[$levelName] = null;
            }
        }
    }

    public function filter()
    {
        $selections = collect($this->selections)->filter();
        $selectionNames = $selections->mapWithKeys(fn ($path, $levelName) => [$levelName.'Name' => $this->dropdowns[$levelName][$path]])->all();
        $selectionNames = [];
        $selectionPaths = $selections->map(fn ($path) => $this->removeChecksumSafety($path))->all();
        $filter = [...$selectionNames, ...$selectionPaths];
        session()->put('area-filter', $filter);
        $this->emit('filterChanged', $filter);

        /*$checksumRemovedSelection = $this->removeChecksumSafety($this->selection);
        session()->put('area-filter', $checksumRemovedSelection);
        $explodedPath = explode('.', $checksumRemovedSelection);
        $filter = array_combine(array_slice($this->hierarchies, 0, count($explodedPath)), $explodedPath);
        $this->emit('filterChanged', $filter);*/
    }

    public function clear()
    {
        session()->forget('area-filter');
        $this->mount();
        $this->emit('filterChanged', []);
    }

    public function render()
    {
        return view('livewire.area-filter');
    }
}
