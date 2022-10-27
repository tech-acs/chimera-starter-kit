<?php

namespace App\Http\Livewire;

use App\Services\AreaTree;
use App\Services\Traits\ChecksumSafetyTrait;
use Illuminate\Support\Facades\DB;
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
        $this->hierarchies = (new AreaTree)->hierarchies;
        $selectionsFromSession = session()->get('area-filter', []);
        $restrictions = []; //['region' => '02', 'constituency' => '02.0201'];
        $heldPath = null;
        $this->dropdowns = array_map(function ($level) use ($selectionsFromSession, $restrictions) {
            global $heldPath;
            $levelName = $this->hierarchies[$level];
            $replacementValue = [];
            if ($level === 0) {
                $replacementValue = $this->areas()->pluck('name', 'path')->all();
            }
            if ($heldPath) {
                $replacementValue = $this->areas($heldPath)->pluck('name', 'path')->all();
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

    private function prev($levelName)
    {
        $key = array_search($levelName, $this->hierarchies);
        return $key === false ? null : $this->hierarchies[$key - 1] ?? null;
    }

    private function next($levelName)
    {
        $key = array_search($levelName, $this->hierarchies);
        return $key === false ? null : $this->hierarchies[$key + 1] ?? null;
    }

    private function nextLevelNames($levelName)
    {
        $currentKey = array_search($levelName, $this->hierarchies);
        return array_slice(array_values($this->hierarchies), $currentKey + 1);
    }

    public function changeHandler($changedLevelName, $selectedPath)
    {
        $this->selections[$changedLevelName] = $selectedPath;
        $nextDropdown = $this->next($changedLevelName);
        if ($nextDropdown) {
            $this->dropdowns[$nextDropdown] = $this
                ->areas($this->removeChecksumSafety($selectedPath))
                ->pluck('name', 'path')
                ->all();
            $nextLevelNames = $this->nextLevelNames($changedLevelName);
            //dump($changedLevelName, $nextLevelNames, $nextDropdown);
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
        $this->emit('updateChart', $filter);
        //dump($filter);
    }

    public function clear()
    {
        session()->forget('area-filter');
        $this->mount();
        $this->emit('updateChart', []);
    }

    public function areas(?string $parentPath = null, string $orderBy = 'name', bool $checksumSafe = true)
    {
        $lquery = empty($parentPath) ? '*{1}' : "$parentPath.*{1}";
        return DB::table('areas')
            ->selectRaw($checksumSafe ? "CONCAT('*', path) AS path, code, name" : 'path, code, name')
            ->whereRaw("path ~ '{$lquery}'")
            ->orderBy($orderBy)
            ->get();
    }

    public function render()
    {
        return view('livewire.area-filter');
    }
}
