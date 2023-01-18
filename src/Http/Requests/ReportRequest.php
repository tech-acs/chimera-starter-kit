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
            'run_every' => 'required_if:enabled,true'
        ];
    }

    public function messages()
    {
        return [
            'run_at.required_if' => 'The run at field is required when scheduling is enabled.',
            'run_every.required_if' => 'The run every field is required when scheduling is enabled.'
        ];
    }
}
