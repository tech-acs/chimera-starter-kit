<?php

namespace Uneca\Chimera\Components;

use Uneca\Chimera\Models\Indicator;
use Illuminate\View\Component;

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
