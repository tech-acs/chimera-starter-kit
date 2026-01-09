<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GaugeMakerRequest extends FormRequest
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
            'gauge_name' => ['required', 'string', 'regex:/^[A-Z][A-Za-z\/]*[A-Za-z]$/', 'unique:gauges,name'],
            'title' => 'required',
            'subtitle' => 'required',
            'data_source' => 'required',
        ];
    }
}
