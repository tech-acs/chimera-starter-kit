<?php

namespace App\Http\Livewire;

use App\Models\AreaHierarchy;
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
            //$areaHierarchy->select_column = "'{$areaHierarchy->select_column}'";
            //$areaHierarchy->where_column = "'{$areaHierarchy->where_column}'";
            $areaHierarchy->save();
        }
        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.column-mapper');
    }
}
