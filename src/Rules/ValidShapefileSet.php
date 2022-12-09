<?php

namespace Uneca\Chimera\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ValidShapefileSet implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
        if (count($value) < 3) {
            $fail('All three shapefile component files are required');
        }
        if (count($value) > 3) {
            $fail('Only the three shapefile component files are required');
        }

        $fileNames = collect(array_map(function ($file) {
            return $file->getClientOriginalName();
        }, $value));
        $haveSameFilenames = $fileNames->map(function ($file) {
            return str($file)->beforeLast('.')->toString();
        })->countBy()->values()->contains(3);

        if (! $haveSameFilenames) {
            $fail('All three files need to have the same filename');
        }

        $requiredComponentsIncluded = collect(['shp', 'shx', 'dbf'])
            ->diff($fileNames->map(function ($file) {
                return str($file)->afterLast('.');
            }))
            ->isNotEmpty();

        if ($requiredComponentsIncluded) {
            $fail('All three shapefile component files (shp, shx & dbf) are required');
        }
    }
}
