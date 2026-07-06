<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MapIndicatorRequest extends FormRequest
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
            'title' => 'required',
            'description' => 'nullable',
            'pages' => ['required_if:published,true', 'array', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'pages.required' => 'You must add the map indicator to at least one page before publishing.',
            'pages.min' => 'You must add the map indicator to at least one page before publishing.',
        ];
    }
}
