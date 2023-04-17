<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Services\DashboardComponentFactory;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Exporter extends Component
{
    protected $listeners = ['updateChart' => 'update'];

    public $indicator;
    public $filter = [];

    public function mount()
    {
        $this->filter = array_merge(
            auth()->user()->areaRestrictionAsFilter(),
            session()->get('area-filter', [])
        );
    }

    public function update(array $filter)
    {
        $this->filter = $filter;
    }

    public function export()
    {
        $indicatorInstance = DashboardComponentFactory::makeIndicator($this->indicator);
        $data = $indicatorInstance->getData($this->filter);

        $file = sys_get_temp_dir() . '/' . Str::replace('.', '_', $this->indicator->slug) . '.csv';
        $writer = SimpleExcelWriter::create($file);
        foreach ($data as $record) {
            $writer->addRow((array)$record);
        }
        return response()->download($file);
    }

    public function render()
    {
        return view('chimera::livewire.exporter');
    }
}
