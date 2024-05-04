<?php

namespace Uneca\Chimera\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ColorPalette
{
    public static function all(): Collection
    {
        return Cache::rememberForever('color-palettes', function () {
            $colorPalettes = collect();
            $colorPalettesDir = resource_path('color_palettes');
            foreach (glob("$colorPalettesDir/*.json", ) as $paletteFile) {
                $colorPalettes[] = json_decode(file_get_contents($paletteFile));
            }
            return $colorPalettes;
        });
    }

    public static function palette(string $paletteName): ?object
    {
        $index = self::all()->search(function ($palette) use ($paletteName) {
            return $palette->name === $paletteName;
        });
        if ($index !== false) {
            return self::all()[$index];
        }
        return null;
    }
}
