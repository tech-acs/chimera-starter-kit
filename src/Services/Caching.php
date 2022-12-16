<?php

namespace Uneca\Chimera\Services;

use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Indicator;

class Caching
{
    public static function makeIndicatorCacheKey(Indicator $indicator, array $filter = [])
    {
        return $indicator->slug . implode('-', array_filter($filter));
    }

    public static function updateIndicatorCache(Indicator $indicator, array $filter = [])
    {
        $chart = IndicatorFactory::make($indicator);
        $freshData = $chart->getData($filter);
        $key = Caching::makeIndicatorCacheKey($indicator, $filter);
        Cache::tags([$indicator->slug, 'timestamps'])->put("$key|timestamp", time());
        return Cache::tags([$indicator->slug, 'indicators'])->put($key, $freshData);
    }
}
