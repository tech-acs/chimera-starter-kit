<?php

namespace App\Services\Interfaces;

interface PieChart
{
    public const PieTraceTemplate = [
        'hoverinfo' => 'x+y',
        //'hovertemplate' => 'Value: %{y:$.2f}',
        'textposition' => 'auto',
        'texttemplate' => "%{value} (%{percent})",
        'type' => 'pie'
    ];
}
