<?php

namespace App\Services\Traits;

use App\Models\Area;

trait Geospatial
{
    private static function findContainingGeometry($level, $geom)
    {
        return Area::ofLevel($level)
            ->whereRaw("ST_Area(ST_Intersection(geom::geometry, $geom)) > 0.70 * ST_Area($geom)")
            ->first();
    }
}
