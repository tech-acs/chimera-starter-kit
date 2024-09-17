<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Attributes\On;
use Uneca\Chimera\Services\DashboardComponentFactory;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Uneca\Chimera\Traits\AreaResolver;

class Exporter extends Component
{
    use AreaResolver;

    public $indicator;
    public string $filterPath = '';

    public function mount()
    {
        $this->update();
    }

    #[On(['filterChanged'])]
    public function update()
    {
        list($this->filterPath,) = $this->areaResolver();
    }

    public function export()
    {
        $indicatorInstance = DashboardComponentFactory::makeIndicator($this->indicator);
        $data = $indicatorInstance->getData($this->filterPath);

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
