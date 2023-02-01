<?php

namespace Uneca\Chimera\Services;

class Helpers
{
    public static function safeDivide($numerator, $denominator, $integerDivision = false) {
        if (is_numeric($denominator) && $denominator > 0) {
            return $integerDivision ? intdiv($numerator, $denominator): ($numerator/$denominator);
        }
        return 0;
    }
}
