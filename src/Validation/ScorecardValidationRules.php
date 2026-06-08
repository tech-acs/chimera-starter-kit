<?php

namespace Uneca\Chimera\Validation;

class ScorecardValidationRules
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*[A-Za-z]$/', 'unique:scorecards,name'],
            'title' => ['required', 'max:255', 'string'],
            'data_source' => 'required',
            //'stub' => ['required', 'string'],
        ];
    }
}
