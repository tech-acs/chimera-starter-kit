<?php

namespace App\View\Components;

use App\Models\Indicator;
use Illuminate\View\Component;

class ChartCard extends Component
{
    public Indicator $indicator;
    /*public string $page;
    public string $chart;
    public string $title;
    public string $description;*/
    public string $mode;
    public string $help;
    public string $fullPageViewRoute;

    public function __construct(Indicator $indicator, $mode = 'grid', $help = 'Put help text here')
    {
        /*$this->page = $page;
        $this->chart = $chart;
        $this->title = $title;
        $this->description = $description;*/
        $this->indicator = $indicator;
        $this->fullPageViewRoute = ''; //route('single', ['page' => $this->indicator->page, 'chart' => '']);
        $this->mode = $mode;
        $this->help = $help;
    }

    public function render()
    {
        return view('components.chart-card');
    }
}
