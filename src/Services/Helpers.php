<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

if (! function_exists('settings')) {
    function settings(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return app('settings');
        }
        $setting = app('settings')->get($key);
        if (! is_null($setting)) {
            try {
                return Crypt::decryptString($setting);
            } catch (\Exception) {
                return null;
            }
        } else {
            return $default;
        }
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

if (! function_exists('toDataFrame')) {
    function toDataFrame(Collection $data)
    {
        $df = collect();
        if ($data->isEmpty()) {
            return $df;
        }
        $data = $data->values();
        $firstRow = $data[0];
        $columns = array_keys($firstRow instanceof Model ? $firstRow->toArray() : (array) $firstRow);
        foreach ($columns as $column) {
            $df[$column] = $data->pluck($column)->all();
        }
        return $df;
    }
}
