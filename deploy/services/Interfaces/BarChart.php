<?php

namespace App\Services\Interfaces;

interface BarChart
{
    public const BarTraceTemplate = [
        'hoverinfo' => 'x+y',
        'textposition' => 'auto',
        'type' => 'bar'
    ];

    public const ValueTraceTemplate = [
        'textposition' => 'auto',
        'texttemplate' => "%{value}",
        'hovertemplate' => "%{value}",
        'type' => 'bar'
    ];

    public const PercentageBarTraceTemplate = [
        'textposition' => 'auto',
        'texttemplate' => "%{value} %",
        'hovertemplate' => "%{value} %",
        'type' => 'bar',
    ];
}
