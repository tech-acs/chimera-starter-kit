<?php

//namespace Uneca\Chimera\Services;

if (! function_exists('settings')) {
    function settings(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return app('settings');
        }
        return app('settings')->get($key, $default);
    }
}

if (! function_exists('safeDivide')) {
    function safeDivide($numerator, $denominator, $integerDivision = false)
    {
        if (is_numeric($denominator) && $denominator > 0) {
            return $integerDivision ? intdiv($numerator, $denominator): ($numerator/$denominator);
        }
        return 0;
    }
}

/*class Helpers
{
    public static function safeDivide($numerator, $denominator, $integerDivision = false) {
        if (is_numeric($denominator) && $denominator > 0) {
            return $integerDivision ? intdiv($numerator, $denominator): ($numerator/$denominator);
        }
        return 0;
    }

    public static function livewireComponentExistsInDashboardNamespace(string $component)
    {
        $components = app(\Livewire\LivewireComponentsFinder::class)->getManifest();
        return in_array($component, array_keys($components));
    }
}*/
