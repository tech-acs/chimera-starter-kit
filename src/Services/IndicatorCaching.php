<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Scorecard;

class IndicatorCaching extends Caching
{
    public function __construct(Scorecard|MapIndicator|Indicator $model, array $filter)
    {
        $this->model = $model;
        $this->instance = DashboardComponentFactory::makeIndicator($model);
        $this->filter = $filter;
        $this->key = 'indicator|' . $this->model->slug . implode('-', array_filter($filter));
    }

    public function tags(): array
    {
        return [$this->model->questionnaire, $this->model->slug, 'indicators'];
    }
}
