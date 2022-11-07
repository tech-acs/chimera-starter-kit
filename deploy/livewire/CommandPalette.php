<?php

namespace App\Http\Livewire;

use App\Models\Indicator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CommandPalette extends Component
{
    const MAX_RESULTS = 6;

    public string $search = '';
    public int $resultCount;
    public int $activeResult = 0;

    public function render()
    {
        $results = Indicator::query()
            ->published()
            ->when(! empty($this->search), function ($builder) {
                $builder
                    ->whereRaw("title->>'en' ilike '%{$this->search}%'")
                    ->orWhereRaw("description->>'en' ilike '%{$this->search}%'");
            })
            ->take(self::MAX_RESULTS)
            ->get()
            ->filter(function ($indicator) {
                return Gate::allows($indicator->permission_name);
            });
        $this->resultCount = $results->count();
        $this->activeResult = 0;
        return view('livewire.command-palette', compact('results'));
    }
}
