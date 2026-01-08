<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'image_urls' => ['required', 'array', 'min:1', 'max:10'],
            'image_urls.*' => ['required', 'url'],
        ];

        if (request()->routeIs('admin.content.image.update')) {
            $rules['id'] = ['required', 'exists:contents,id'];
        }

        return $rules;
    }
}
