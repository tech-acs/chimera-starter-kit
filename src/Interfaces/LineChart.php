<?php

namespace Uneca\Chimera\Interfaces;

interface LineChart
{
    public const LineTraceTemplate = [
        'hoverinfo' => 'x+y',
        //'hovertemplate' => 'Value: %{y:$.2f}',
        'textposition' => 'auto',
        'type' => 'scatter',
        'mode' => 'lines+markers'
    ];
}
