<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class Caching
{
    public static function makeIndicatorCacheKey(string $indicator, array $filter)
    {
        return $indicator . implode('-', array_filter($filter));
    }

    public static function makeAreaListCacheKey(string $connection, string $areaType, string $parentArea)
    {
        return "$connection-area-list-$areaType-$parentArea";
    }

    public static function updateIndicator(string $connection, string $indicator, array $filter, $ttl = null)
    {
        $startTime = time();
        $indicatorInstance = IndicatorFactory::make($connection, $indicator);
        $freshData = $indicatorInstance->getCollection($filter);
        $key = static::makeIndicatorCacheKey($indicator, $filter);
        $endTime = time();
        dump("Took " . ($endTime - $startTime) . " seconds to update $key");

        $stamp = now()->format('Y-m-d H:i:s');
        Cache::tags([$indicatorInstance->connection, 'timestamp'])->put($key, $stamp);
        return Cache::tags([$indicatorInstance->connection, 'indicator'])->put($key, $freshData, $ttl);
    }

    public static function updateAreaList(string $connection, string $areaType, ?string $parentArea)
    {
        $startTime = time();
        $freshData = DataSource::getAreaList($connection, $areaType, $parentArea);
        $key = static::makeAreaListCacheKey($connection, $areaType, $parentArea);
        $endTime = time();
        dump("Took " . ($endTime - $startTime) . " seconds to update $key");

        $stamp = now()->format('Y-m-d H:i:s');
        Cache::tags([$connection, 'timestamp'])->put($key, $stamp);
        return Cache::tags([$connection, 'area-list'])->put($key, $freshData);
    }
}
