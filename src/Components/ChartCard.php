<?php

namespace Uneca\Chimera\Components;

use Illuminate\View\Component;
use Uneca\Chimera\Models\Indicator;

class ChartCard extends Component
{
    public Indicator $indicator;

    public string $mode;

    public function __construct(Indicator $indicator, $mode = 'grid')
    {
        $this->indicator = $indicator;
        $this->mode = $mode;
    }

    public function render()
    {
        return view('chimera::components.chart-card');
    }
}
