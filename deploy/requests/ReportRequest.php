<?php

namespace App\Http\Requests;

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
            'schedule' => 'required_if:enabled,true'
        ];
    }

    public function messages()
    {
        return ['schedule.required_if' => 'The schedule field is required when the report is enabled.'];
    }
}
