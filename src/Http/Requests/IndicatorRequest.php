<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Uneca\Chimera\Enums\IndicatorScope;

class IndicatorRequest extends FormRequest
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
            'description' => 'required',
            'scope' => 'required',
            'pages' => [
                Rule::requiredIf(fn () => $this->boolean('published')
                    && $this->input('scope') !== IndicatorScope::AreaInsights->value),
                'array',
                'min:1',
            ],
        ];
    }

    public function messages()
    {
        return [
            'pages.required' => 'You must add the indicator to at least one page before publishing.',
            'pages.min' => 'You must add the indicator to at least one page before publishing.',
        ];
    }
}
