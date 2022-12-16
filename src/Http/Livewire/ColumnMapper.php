<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Models\AreaHierarchy;
use Livewire\Component;

class ColumnMapper extends Component
{
    public $areaHierarchies;
    public string $message = '';

    protected $rules = [
        'areaHierarchies.*.where_column' => 'required',
        'areaHierarchies.*.select_column' => 'required',
    ];

    public function mount()
    {
        $this->areaHierarchies = AreaHierarchy::orderBy('index')->get();
    }

    public function save()
    {
        $this->validate();

        foreach ($this->areaHierarchies as $areaHierarchy) {
            $areaHierarchy->save();
        }
        $this->emit('saved');
    }

    public function render()
    {
        return view('chimera::livewire.column-mapper');
    }
}
