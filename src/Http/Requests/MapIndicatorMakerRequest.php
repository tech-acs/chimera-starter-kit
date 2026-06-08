<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Uneca\Chimera\Validation\MapIndicatorValidationRules;

class MapIndicatorMakerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return MapIndicatorValidationRules::rules();
    }
}
