<?php

namespace Uneca\Chimera\Traits;

trait PlotlyDefaults
{
    public const DEFAULT_CONFIG = [
        'responsive' => true,
        'displaylogo' => false,
        'modeBarButtonsToRemove' => ['select2d', 'lasso2d', 'autoScale2d', 'hoverClosestCartesian', 'hoverCompareCartesian'],
    ];

    public const DEFAULT_LAYOUT = [
        "showlegend" => true,
        "legend" => [
            "orientation" => "h",
            "x" => 0,
            "y" => 1.12,
        ],
        "xaxis" => [
            "type" => "category",
            "tickmode" => "linear",
            "automargin" => true,
        ],
        "margin" => [
            "l" => 60,
            "r" => 10,
            "t" => 10,
            "b" => 40,
        ],
        "modebar" => [
            "orientation" => "v",
            "color" => "white",
            "bgcolor" => "darkgray",
        ],
        "dragmode" => "pan",
    ];
}
