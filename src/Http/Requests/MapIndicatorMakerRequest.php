<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MapIndicatorMakerRequest extends FormRequest
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
            'map_indicator_name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*[A-Za-z]$/', 'unique:map_indicators,name'],
            'title' => 'required',
            'description' => 'nullable',
            'data_source' => 'required',
        ];
    }
}
