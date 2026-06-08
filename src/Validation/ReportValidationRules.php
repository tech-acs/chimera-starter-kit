<?php

namespace Uneca\Chimera\Validation;

class ReportValidationRules
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*[A-Za-z]$/', 'unique:reports,name'],
            'title' => ['required', 'max:255', 'string'],
            'description' => ['nullable', 'max:255', 'string'],
            'data_source' => 'required',
        ];
    }
}
