<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndicatorMakerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'indicator_name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*[A-Za-z]$/', 'unique:indicators,name'],
            'title' => 'required',
            'description' => 'required',
            'data_source' => 'required',
            'chosen_chart_type' => 'required',
            'includeSampleCode' => 'required',
        ];
    }
}
