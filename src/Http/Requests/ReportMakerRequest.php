<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Uneca\Chimera\Validation\ReportValidationRules;

class ReportMakerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return ReportValidationRules::rules();
    }
}
