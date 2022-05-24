<?php

namespace App\Http\Livewire;

use App\Models\AreaRestriction;
use App\Models\User;
use App\Services\Area;
use App\Services\Traits\ChecksumSafetyTrait;
use Illuminate\Support\Collection;
use Livewire\Component;

class AreaRestrictionManager extends Component
{
    use ChecksumSafetyTrait;

    public $user;
    public ?string $connection = null;
    public Collection $areas;
    public array $selections = [];
    public array $areaRestrictions = [];

    public function mount()
    {
        $areaRepository = new Area($this->connection);
        $levels = $areaRepository->levels();
        $levels->pop(); // We do not have a select for the last level (EA). So, drop it!
        $sessionFilter = session()->get('area-filter', []);
        $previouslySet = $this->user->areaRestrictions()->first();
        //dump($previouslySet);
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
        $smallest = (new Area())->resolveSmallestFilter($filter);

        AreaRestriction::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'connection' => $this->connection,
            ],
            [
                'code' => $smallest->code,
                'name' => $smallest->name ?? null,
            ]
        );
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

    public function render()
    {
        return view('livewire.area-restriction-manager');
    }
}
