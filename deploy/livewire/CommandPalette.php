<?php

namespace App\Http\Livewire;

use App\Models\Indicator;
use Livewire\Component;

class CommandPalette extends Component
{
    const MAX_RESULTS = 6;

    public string $search = '';
    //public array $results;

    /*public function mount()
    {
        $this->results = Indicator::take(self::MAX_RESULTS)
            ->get()
            ->mapWithKeys(function ($indicator) {
                return [$indicator->slug => [$indicator->title, $indicator->description]];
            })
            ->all();
    }

    public function search()
    {
        //$this->results = Indicator::where('name', 'ilike', "$this->search%")->paginate(config('chimera.records_per_page'))->all();
        return Indicator::take(self::MAX_RESULTS)
            ->where('name', 'ilike', "$this->search%")
            ->get()
            ->mapWithKeys(function ($indicator) {
                return [$indicator->slug => [$indicator->title, $indicator->description]];
            })
            ->all();
    }*/

    public function render()
    {
        $results = Indicator::take(self::MAX_RESULTS)
            ->when(! empty($this->search), function ($builder) {
                $builder
                    ->whereRaw("title->>'en' ilike '%{$this->search}%'")
                    ->orWhereRaw("description->>'en' ilike '%{$this->search}%'");
                // description->>'en' ilike 'c%'
            })->get();
        //dump($results->toSql(), $results->getBindings());
        return view('livewire.command-palette', compact('results'));
    }
}
