<?php

namespace Uneca\Chimera\Services;

/*
 * Sourced from https://github.com/xi/apca-introduction
 *
 * As per WCAG 3.0 (https://www.w3.org/TR/wcag-3.0/)
*/

class APCA
{
    const CONTRAST_THRESHOLD = 45;

    public static function sRGBtoY(array $srgb)
    {
        $r = pow($srgb[0] / 255, 2.4);
        $g = pow($srgb[1] / 255, 2.4);
        $b = pow($srgb[2] / 255, 2.4);
        $y = 0.2126729 * $r + 0.7151522 * $g + 0.0721750 * $b;

        if ($y < 0.022) {
            $y += pow(0.022 - $y, 1.414);
        }
        return $y;
    }

    public static function contrast(string $foregroundColor, string $backgroundColor, bool $returnAbsoluteValue = true): int
    {
        $yfg = self::sRGBtoY(self::hexToRGB($foregroundColor));
        $ybg = self::sRGBtoY(self::hexToRGB($backgroundColor));
        $c = 1.14;

        if ($ybg > $yfg) {
            $c *= pow($ybg, 0.56) - pow($yfg, 0.57);
        } else {
            $c *= pow($ybg, 0.65) - pow($yfg, 0.62);
        }

        if (abs($c) < 0.1) {
            return 0;
        } else if ($c > 0) {
            $c -= 0.027;
        } else {
            $c += 0.027;
        }

        return $returnAbsoluteValue ? abs((int)($c * 100)) : (int)($c * 100);
    }

    public static function hexToRGB(string $hex): array
    {
        return sscanf(strtolower($hex), "#%02x%02x%02x");
    }

    public static function decideBlackOrWhiteTextColor(string $backgroundColor, bool $returnTailwindClassName = true): string
    {
        $black = ['hex' => '#000000', 'tailwind' => 'text-black'];
        $white = ['hex' => '#ffffff', 'tailwind' => 'text-white'];
        $contrastRatio = self::contrast($white['hex'], $backgroundColor);
        if ($contrastRatio > self::CONTRAST_THRESHOLD) {
            return $white[$returnTailwindClassName ? 'tailwind' : 'hex'];
        } else {
            return $black[$returnTailwindClassName ? 'tailwind' : 'hex'];
        }
    }
}
