<?php

namespace Uneca\Chimera\Validation;

class GaugeValidationRules
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*[A-Za-z]$/', 'unique:gauges,name'],
            'title' => ['required', 'max:255', 'string'],
            'subtitle' => ['required', 'max:255', 'string'],
            'data_source' => 'required',
        ];
    }
}
