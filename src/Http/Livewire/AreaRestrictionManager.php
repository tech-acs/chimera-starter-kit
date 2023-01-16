<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Models\AreaRestriction;
use Uneca\Chimera\Models\User;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Traits\ChecksumSafetyTrait;
use Illuminate\Support\Collection;
use Livewire\Component;

class AreaRestrictionManager extends AreaFilter
{
    use ChecksumSafetyTrait;

    public User $user;

    public function mount()
    {
        $areaTree = new AreaTree(removeLastNLevels: 1);
        $subject = null;
        $previousRestrictions = $this->user->areaRestrictionAsFilter();
        $this->dropdowns = array_map(function ($level) use ($previousRestrictions, $areaTree, &$subject) {
            $dropdown = ['list' => [], 'selected' => null, 'restricted' => null];
            $levelName = $areaTree->hierarchies[$level];
            if ($level === 0) {
                $dropdown['list'] = $areaTree->areas(checksumSafe: true)->pluck('name', 'path')->all();
            }
            if ($subject) {
                $dropdown['list'] = $areaTree->areas($subject, checksumSafe: true)->pluck('name', 'path')->all();
                $subject = null;
            }
            if (array_key_exists($levelName, $previousRestrictions)) {
                $subject = $previousRestrictions[$levelName];
                $dropdown['selected'] = $this->addChecksumSafety($subject);
            }
            return $dropdown;
        }, array_flip($areaTree->hierarchies));
    }

    public function filter()
    {
        $areaRestrictions = collect($this->dropdowns)
            ->reject(fn ($dropdown) => empty($dropdown['selected']))
            ->mapWithKeys(fn ($dropdown, $key) => [$key => ['level' => AreaTree::levelFromPath($dropdown['selected']), 'path' => $this->removeChecksumSafety($dropdown['selected'])]]);
        $this->user->areaRestrictions()->delete();
        $this->user->areaRestrictions()->createMany($areaRestrictions->values());
    }

    public function render()
    {
        return view('chimera::livewire.area-restriction-manager');
    }
}
