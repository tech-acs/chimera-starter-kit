<?php

namespace Uneca\Chimera\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Uneca\Chimera\Models\Page;

class PageRequest extends FormRequest
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

    protected function passedValidation()
    {
        $this->merge([
            'for' => $this->type,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $slug = str()->slug($this->title) . '-' . strtolower($this->type);
        $existingPage = $this->route('page');
        return [
            'title' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) use ($existingPage, $slug) {
                    $query = Page::whereSlug($slug)->when($existingPage, function ($query) use ($existingPage) {
                        $query->whereNot('id', $existingPage->id);
                    });
                    if ($query->exists()) {
                        $fail("The {$attribute} already exists for this page type.");
                    }
                },
            ],
            'type' => 'required',
        ];
    }
}
