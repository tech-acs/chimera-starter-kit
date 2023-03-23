<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Models\Indicator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CommandPalette extends Component
{
    const MAX_RESULTS = 5;

    public string $search = '';
    public int $resultCount;
    public int $activeResult = 0;

    public function render()
    {
        $locale = app()->getLocale();
        try {
            $results = Indicator::query()
                ->published()
                ->when(! empty($this->search), function ($builder) use ($locale) {
                    $builder->where(function ($builder) use ($locale) {
                        $builder->whereRaw("title->>'{$locale}' ilike '%{$this->search}%'")
                            ->orWhereRaw("description->>'{$locale}' ilike '%{$this->search}%'");
                    });
                })
                ->orderByRaw("title->>'{$locale}'")
                ->get()
                ->filter(function ($indicator) {
                    return Gate::allows($indicator->permission_name);
                })
                ->take(self::MAX_RESULTS);
        } catch (\Throwable $throwable) {
            $results = collect([]);
        }
        $this->resultCount = $results->count();
        $this->activeResult = 0;
        return view('chimera::livewire.command-palette', compact('results'));
    }
}
