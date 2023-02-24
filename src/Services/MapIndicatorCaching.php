<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Models\Scorecard;

class MapIndicatorCaching extends Caching
{
    public function __construct(Scorecard|MapIndicator|Indicator|Questionnaire $model, array $filter)
    {
        $this->model = $model;
        $this->instance = DashboardComponentFactory::makeMapIndicator($model);
        $this->filter = $filter;
        $this->key = $this->model->slug . implode('-', array_filter($filter));
    }

    public function tags(): array
    {
        return ['map-indicators', $this->model->questionnaire];
    }
}
