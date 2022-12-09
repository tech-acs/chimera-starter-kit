<?php

namespace Uneca\Chimera\Traits;

trait ChecksumSafetyTrait
{
    private function addChecksumSafety(?string $str): ?string
    {
        return $str ? '*' . $str : null;
    }

    private function removeChecksumSafety(string $str): string
    {
        return ltrim($str, '*');
    }
}
