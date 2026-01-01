<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
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
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $brand = $this->route('brand');
            return [
                'name'   => 'sometimes|string|unique:brands,name,|max:255',
                'status' => 'nullable|in:active,inactive',
                'is_featured' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'banners.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ];
        }
        return [
            'name'   => 'required|string|unique:brands,name|max:255',
            'status' => 'nullable|in:active,inactive',
            'is_featured' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'banners.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',

        ];
    }
}
