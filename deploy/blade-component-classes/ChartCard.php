<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ChartCard extends Component
{
    public string $page;
    public string $chart;
    public string $title;
    public string $description;
    public string $mode;
    public string $help;
    public string $fullPageViewRoute;

    public function __construct($page, $chart, $title, $description, $mode = 'grid', $help = 'Put help text here')
    {
        $this->page = $page;
        $this->chart = $chart;
        $this->fullPageViewRoute = route('single', ['page' => $page, 'chart' => $chart]);
        $this->title = $title;
        $this->description = $description;
        $this->mode = $mode;
        $this->help = $help;
    }

    public function render()
    {
        return view('components.chart-card');
    }
}
