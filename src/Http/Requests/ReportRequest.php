<?php

namespace Uneca\Chimera\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
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

    public function rules()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'run_at' => 'required_if:enabled,true',
            'run_every' => 'required_if:enabled,true',
            'pages' => ['required_if:published,true', 'array', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'run_at.required_if' => 'The run at field is required when scheduling is enabled.',
            'run_every.required_if' => 'The run every field is required when scheduling is enabled.',
            'pages.required_if' => 'Add it to at least one page before publishing.',
            'pages.required' => 'Add it to at least one page before publishing.',
            'pages.min' => 'Add it to at least one page before publishing.',
        ];
    }
}
