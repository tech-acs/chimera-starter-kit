<?php

namespace App\Http\Livewire;

use App\Services\IndicatorFactory;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Exporter extends Component
{
    protected $listeners = ['updateChart' => 'update'];

    //public $page;
    public $chart;
    public $filter = [];

    public function update(array $filter)
    {
        $this->filter = $filter;
    }

    public function export()
    {
        //$connection = config("chimera.pages.{$this->page}.connection");
        //$data = DataSource::getCollection($this->chart)($connection, $this->filter);
        $indicatorInstance = IndicatorFactory::make($this->chart);
        $data = $indicatorInstance->getData($this->filter);

        $file = sys_get_temp_dir() . '/' . Str::replace('.', '_', $this->chart) . '.csv';
        $writer = SimpleExcelWriter::create($file);
        foreach ($data as $record) {
            $writer->addRow((array)$record);
        }
        return response()->download($file);
    }

    public function render()
    {
        return view('livewire.exporter');
    }
}
