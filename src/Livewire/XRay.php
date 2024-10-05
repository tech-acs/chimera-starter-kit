<?php

namespace Uneca\Chimera\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Livewire\Component;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\DashboardComponentFactory;

class XRay extends Component
{
    public Collection $groupedIndicators;
    public string $output = '';
    public int $lineNumber = 0;
    public string $filterPath = '';

    public function mount()
    {
        $this->groupedIndicators = Indicator::published()
            ->get()
            ->map(function (Indicator $indicator) {
                return (object)[
                    'id' => $indicator->id,
                    'name' => str($indicator->name)->after('/')->toString(),
                    'title' => $indicator->title,
                    'data_source' => str($indicator->name)->before('/')->toString()
                ];
            })
            ->groupBy('data_source');
    }

    public function checkLogEntries()
    {
        $xrays = file(config('chimera.xray_file'));
        $count = count($xrays);

        while ($this->lineNumber < $count) {
            $line = $xrays[$this->lineNumber];
            $parsedLine = json_decode($line, true);
            if (count($parsedLine) >= 5) {
                $this->dispatch('x-ray-film', film: [
                    'name' => $parsedLine['name'],
                    'sql' => $parsedLine['sql'],
                    'queryResult' => $parsedLine['queryResult'],
                    'joinType' => $parsedLine['joinType'],
                    'finalResult' => $parsedLine['finalResult'],
                    'index' => $this->lineNumber
                ]);
            }
            $this->lineNumber++;
        }
    }

    public function takeXRay(Indicator $indicator)
    {
        Context::addHidden('x-ray', true);

        $indicatorComponent = DashboardComponentFactory::makeIndicator($indicator);
        $indicatorComponent->getData($this->filterPath);
    }

    public function render()
    {
        return view('chimera::livewire.x-ray');
    }
}
