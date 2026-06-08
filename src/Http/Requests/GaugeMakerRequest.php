<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Uneca\Chimera\Validation\GaugeValidationRules;

class GaugeMakerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return GaugeValidationRules::rules();
    }
}
