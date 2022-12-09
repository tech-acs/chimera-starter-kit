<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Support\Collection;

trait ValueSetMapable
{
    public function valueToLabel(array $mapping, Collection $values)
    {
        return $values->map(function ($item) use ($mapping) {
            return $mapping[$item];
        });
    }
}
