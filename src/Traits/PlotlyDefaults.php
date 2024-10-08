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
            "tickmode" => "auto",
            "automargin" => true,
        ],
        "yaxis2" => [
            "side" => "right",
            "overlaying" => "y",
            "showgrid" => false,
        ],
        "margin" => [
            "l" => 60,
            "r" => 30,
            "t" => 15,
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
