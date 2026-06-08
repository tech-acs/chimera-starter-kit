<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Uneca\Chimera\Validation\IndicatorValidationRules;

class IndicatorMakerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return IndicatorValidationRules::rules();
    }
}
