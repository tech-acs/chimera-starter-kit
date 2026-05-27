<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Component;
use Uneca\Chimera\Models\AreaHierarchy;

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
